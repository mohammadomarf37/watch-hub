<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    // Get all orders for authenticated user
    public function index(Request $request)
    {
        $query = $request->user()->orders()->with(['items.watch', 'items.variant']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Get single order details
    public function show(Request $request, $id)
    {
        $order = $request->user()->orders()
            ->with([
                'items.watch' => function ($q) {
                    $q->with(['brand', 'images' => function ($img) {
                        $img->where('is_primary', true);
                    }]);
                },
                'items.variant',
                'shippingAddress',
                'billingAddress',
                'shippingMethod',
                'paymentMethod',
                'coupon',
                'statusHistories' => function ($q) {
                    $q->orderBy('created_at', 'desc');
                }
            ])
            ->find($id);

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

    // Get order by order number
    public function getByOrderNumber(Request $request, $orderNumber)
    {
        $order = $request->user()->orders()
            ->with([
                'items.watch' => function ($q) {
                    $q->with(['brand', 'images' => function ($img) {
                        $img->where('is_primary', true);
                    }]);
                },
                'items.variant',
                'shippingAddress',
                'billingAddress',
                'shippingMethod',
                'paymentMethod',
                'coupon',
                'statusHistories'
            ])
            ->where('order_number', $orderNumber)
            ->first();

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

    // Cancel order
    public function cancel(Request $request, $id)
    {
        $order = $request->user()->orders()->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Order cannot be cancelled. Current status: ' . $order->status
            ], 400);
        }

        // Check if order can be cancelled (time limit - 24 hours)
        if ($order->created_at->diffInHours(now()) > 24) {
            return response()->json([
                'success' => false,
                'message' => 'Order can only be cancelled within 24 hours of placement'
            ], 400);
        }

        // Start transaction
        \DB::beginTransaction();

        try {
            // Update order status
            $oldStatus = $order->status;
            $order->update(['status' => 'cancelled']);

            // Restore stock
            foreach ($order->items as $item) {
                $variant = $item->variant;
                $variant->increment('stock', $item->quantity);
            }

            // Log status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status_from' => $oldStatus,
                'status_to' => 'cancelled',
                'changed_by' => $request->user()->id,
                'notes' => 'Order cancelled by user'
            ]);

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'data' => $order->fresh()
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel order: ' . $e->getMessage()
            ], 500);
        }
    }

    // Track order status
    public function track(Request $request, $id)
    {
        $order = $request->user()->orders()->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Get status timeline
        $timeline = [
            'order_placed' => [
                'status' => 'Order Placed',
                'completed' => true,
                'date' => $order->placed_at ?? $order->created_at
            ],
            'order_confirmed' => [
                'status' => 'Order Confirmed',
                'completed' => in_array($order->status, ['processing', 'shipped', 'delivered']),
                'date' => $order->statusHistories()->where('status_to', 'processing')->first()?->created_at
            ],
            'packed' => [
                'status' => 'Packed',
                'completed' => in_array($order->status, ['shipped', 'delivered']),
                'date' => $order->statusHistories()->where('status_to', 'shipped')->first()?->created_at
            ],
            'shipped' => [
                'status' => 'Shipped',
                'completed' => in_array($order->status, ['delivered']),
                'date' => $order->statusHistories()->where('status_to', 'delivered')->first()?->created_at
            ],
            'delivered' => [
                'status' => 'Delivered',
                'completed' => $order->status == 'delivered',
                'date' => $order->status == 'delivered' ? $order->updated_at : null
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $order,
                'timeline' => $timeline,
                'current_status' => $order->status,
                'estimated_delivery' => $order->created_at->addDays(10)
            ]
        ]);
    }

    // Get order status history
    public function getStatusHistory(Request $request, $id)
    {
        $order = $request->user()->orders()->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $history = $order->statusHistories()
            ->with('changedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // Get order invoice (for download)
    public function invoice(Request $request, $id)
    {
        $order = $request->user()->orders()
            ->with([
                'items.watch' => function ($q) {
                    $q->with('brand');
                },
                'items.variant',
                'shippingAddress',
                'billingAddress',
                'shippingMethod',
                'paymentMethod',
                'coupon'
            ])
            ->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Generate invoice data
        $invoice = [
            'order_number' => $order->order_number,
            'date' => $order->created_at->format('Y-m-d'),
            'customer' => [
                'name' => $order->user->name,
                'email' => $order->user->email,
                'phone' => $order->user->phone
            ],
            'shipping_address' => $order->shippingAddress,
            'billing_address' => $order->billingAddress,
            'items' => $order->items->map(function ($item) {
                return [
                    'product' => $item->watch->brand->name . ' ' . $item->watch->model,
                    'variant' => $item->variant->color . ' / ' . $item->variant->size,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total
                ];
            }),
            'subtotal' => $order->subtotal,
            'discount' => $order->discount_amount,
            'shipping' => $order->shipping_cost,
            'tax' => $order->tax_amount,
            'total' => $order->total_amount,
            'currency' => $order->currency_code,
            'payment_method' => $order->paymentMethod?->brand . ' ****' . $order->paymentMethod?->last_four,
            'shipping_method' => $order->shippingMethod?->name,
            'status' => $order->status
        ];

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    // Get order summary (for dashboard)
    public function summary(Request $request)
    {
        $user = $request->user();

        $summary = [
            'total_orders' => $user->orders()->count(),
            'pending_orders' => $user->orders()->where('status', 'pending')->count(),
            'processing_orders' => $user->orders()->where('status', 'processing')->count(),
            'shipped_orders' => $user->orders()->where('status', 'shipped')->count(),
            'delivered_orders' => $user->orders()->where('status', 'delivered')->count(),
            'cancelled_orders' => $user->orders()->where('status', 'cancelled')->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'recent_orders' => $user->orders()
                ->with(['items.watch' => function ($q) {
                    $q->with('brand');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $summary
        ]);
    }
}
