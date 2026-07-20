<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderStatusController extends Controller
{
    // Update order status (Admin only)
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if status is already same
        if ($order->status === $request->status) {
            return response()->json([
                'success' => false,
                'message' => 'Order is already in ' . $request->status . ' status'
            ], 400);
        }

        // Validate status transition
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        if (!in_array($request->status, $allowedTransitions[$order->status])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status transition from ' . $order->status . ' to ' . $request->status
            ], 400);
        }

        // Start transaction
        \DB::beginTransaction();

        try {
            $oldStatus = $order->status;

            // Handle stock when cancelling or refunding
            if ($request->status === 'cancelled' || $request->status === 'refunded') {
                foreach ($order->items as $item) {
                    $variant = $item->variant;
                    $variant->increment('stock', $item->quantity);
                }
            }

            // Handle stock when processing (reduce stock)
            if ($request->status === 'processing' && $oldStatus === 'pending') {
                foreach ($order->items as $item) {
                    $variant = $item->variant;
                    if ($variant->stock < $item->quantity) {
                        \DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Not enough stock for: ' . $item->watch->model,
                            'data' => [
                                'variant_id' => $item->variant_id,
                                'available' => $variant->stock,
                                'requested' => $item->quantity
                            ]
                        ], 400);
                    }
                    $variant->decrement('stock', $item->quantity);
                }
            }

            // Update order status
            $order->update(['status' => $request->status]);

            // If status is delivered, update payment status
            if ($request->status === 'delivered') {
                $order->update(['payment_status' => 'paid']);
            }

            // Log status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => $oldStatus,
                'status_to' => $request->status,
                'changed_by' => $request->user()->id,
                'notes' => $request->notes ?? 'Status updated by admin'
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'data' => [
                    'order' => $order->fresh(),
                    'previous_status' => $oldStatus,
                    'new_status' => $request->status
                ]
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update order status: ' . $e->getMessage()
            ], 500);
        }
    }

    // Bulk update order status
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'exists:orders,id',
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = Order::whereIn('id', $request->order_ids)->get();

        if ($orders->count() === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No orders found'
            ], 404);
        }

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($orders as $order) {
            // Skip if same status
            if ($order->status === $request->status) {
                $failed++;
                $errors[] = 'Order ' . $order->order_number . ' is already in ' . $request->status . ' status';
                continue;
            }

            // Validate transition
            $allowedTransitions = [
                'pending' => ['processing', 'cancelled'],
                'processing' => ['shipped', 'cancelled'],
                'shipped' => ['delivered', 'cancelled'],
                'delivered' => ['refunded'],
                'cancelled' => [],
                'refunded' => [],
            ];

            if (!in_array($request->status, $allowedTransitions[$order->status])) {
                $failed++;
                $errors[] = 'Invalid transition for order ' . $order->order_number . ' from ' . $order->status . ' to ' . $request->status;
                continue;
            }

            try {
                \DB::beginTransaction();

                $oldStatus = $order->status;

                // Handle stock
                if ($request->status === 'cancelled' || $request->status === 'refunded') {
                    foreach ($order->items as $item) {
                        $variant = $item->variant;
                        $variant->increment('stock', $item->quantity);
                    }
                }

                if ($request->status === 'processing' && $oldStatus === 'pending') {
                    foreach ($order->items as $item) {
                        $variant = $item->variant;
                        if ($variant->stock < $item->quantity) {
                            \DB::rollBack();
                            $failed++;
                            $errors[] = 'Not enough stock for order ' . $order->order_number;
                            continue 2;
                        }
                        $variant->decrement('stock', $item->quantity);
                    }
                }

                $order->update(['status' => $request->status]);

                if ($request->status === 'delivered') {
                    $order->update(['payment_status' => 'paid']);
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status_from' => $oldStatus,
                    'status_to' => $request->status,
                    'changed_by' => $request->user()->id,
                    'notes' => $request->notes ?? 'Bulk status update'
                ]);

                \DB::commit();
                $updated++;
            } catch (\Exception $e) {
                \DB::rollBack();
                $failed++;
                $errors[] = 'Failed to update order ' . $order->order_number . ': ' . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Bulk status update completed',
            'data' => [
                'total' => $orders->count(),
                'updated' => $updated,
                'failed' => $failed,
                'errors' => $errors
            ]
        ]);
    }

    // Get available statuses for an order
    public function getAvailableStatuses($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'cancelled'],
            'delivered' => ['refunded'],
            'cancelled' => [],
            'refunded' => [],
        ];

        $available = $allowedTransitions[$order->status] ?? [];

        // Add current status
        $available = array_merge([$order->status], $available);

        // Get all statuses with labels
        $statuses = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            'refunded' => 'Refunded'
        ];

        $result = [];
        foreach ($available as $status) {
            $result[] = [
                'value' => $status,
                'label' => $statuses[$status] ?? $status,
                'is_current' => $status === $order->status
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_status' => $order->status,
                'available_statuses' => $result
            ]
        ]);
    }

    // Get all statuses with counts (for dashboard)
    public function getStatusCounts(Request $request)
    {
        $query = Order::query();

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $counts = [
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'processing' => $query->clone()->where('status', 'processing')->count(),
            'shipped' => $query->clone()->where('status', 'shipped')->count(),
            'delivered' => $query->clone()->where('status', 'delivered')->count(),
            'cancelled' => $query->clone()->where('status', 'cancelled')->count(),
            'refunded' => $query->clone()->where('status', 'refunded')->count(),
            'total' => $query->clone()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    }

    // Get order status timeline (all status transitions)
    public function getTimeline($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $timeline = $order->statusHistories()
            ->with('changedBy')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'current_status' => $order->status,
                'timeline' => $timeline
            ]
        ]);
    }
}
