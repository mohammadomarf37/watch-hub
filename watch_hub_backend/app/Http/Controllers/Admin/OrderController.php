<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Get all orders with filters
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items.watch', 'shippingAddress']);

        // Search by order number or user
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by amount range
        if ($request->has('min_amount')) {
            $query->where('total_amount', '>=', $request->min_amount);
        }
        if ($request->has('max_amount')) {
            $query->where('total_amount', '<=', $request->max_amount);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Get single order details
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items' => function ($q) {
                $q->with(['watch' => function ($sub) {
                    $sub->with('brand');
                }, 'variant']);
            },
            'shippingAddress',
            'billingAddress',
            'shippingMethod',
            'paymentMethod',
            'coupon',
            'payments',
            'statusHistories' => function ($q) {
                $q->with('changedBy')->orderBy('created_at', 'desc');
            }
        ])->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    // Update order status
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

        $order = Order::with('items.variant')->find($id);

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

        \DB::beginTransaction();

        try {
            $oldStatus = $order->status;

            // Handle stock for cancelled/refunded
            if ($request->status === 'cancelled' || $request->status === 'refunded') {
                foreach ($order->items as $item) {
                    $item->variant->increment('stock', $item->quantity);
                }
            }

            // Handle stock for processing (reduce)
            if ($request->status === 'processing' && $oldStatus === 'pending') {
                foreach ($order->items as $item) {
                    if ($item->variant->stock < $item->quantity) {
                        \DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Not enough stock for: ' . $item->watch->model,
                            'data' => [
                                'variant_id' => $item->variant_id,
                                'available' => $item->variant->stock,
                                'requested' => $item->quantity
                            ]
                        ], 400);
                    }
                    $item->variant->decrement('stock', $item->quantity);
                }
            }

            $order->update(['status' => $request->status]);

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

    // Delete order
    public function destroy($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Restore stock if order is not cancelled or refunded
        if (!in_array($order->status, ['cancelled', 'refunded'])) {
            foreach ($order->items as $item) {
                $item->variant->increment('stock', $item->quantity);
            }
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    // Get order statistics
    public function stats()
    {
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'refunded' => Order::where('status', 'refunded')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total_amount'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'this_month_orders' => Order::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Get orders by date range
    public function getByDateRange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $orders = Order::whereBetween('created_at', [
            $request->from_date,
            $request->to_date . ' 23:59:59'
        ])->with(['user', 'items'])->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Export orders (CSV format)
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->get();

        // Format data for CSV
        $data = [];
        foreach ($orders as $order) {
            $data[] = [
                'Order Number' => $order->order_number,
                'Customer' => $order->user->name,
                'Email' => $order->user->email,
                'Total' => $order->total_amount,
                'Status' => $order->status,
                'Payment Status' => $order->payment_status,
                'Date' => $order->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
            'message' => 'Export data ready'
        ]);
    }
}
