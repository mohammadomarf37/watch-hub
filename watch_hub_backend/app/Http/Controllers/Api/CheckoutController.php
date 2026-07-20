<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ShippingMethod;
use App\Models\WatchVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // Get checkout summary
    public function summary(Request $request)
    {
        $user = $request->user();

        // Get cart
        $cart = $user->cart()->with(['items.variant.watch'])->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Calculate totals
        $subtotal = 0;
        $items = [];

        foreach ($cart->items as $item) {
            $watch = $item->variant->watch;
            $price = ($watch->discounted_price ?? $watch->base_price) + $item->variant->additional_price;
            $total = $price * $item->quantity;
            $subtotal += $total;

            $items[] = [
                'id' => $item->id,
                'watch_name' => $watch->brand->name . ' ' . $watch->model,
                'variant' => $item->variant->color . ' / ' . $item->variant->size,
                'quantity' => $item->quantity,
                'price' => $price,
                'total' => $total
            ];
        }

        // Get addresses
        $addresses = $user->addresses()->get();
        $defaultAddress = $addresses->where('is_default', true)->first();

        // Get shipping methods
        $shippingMethods = ShippingMethod::where('is_active', true)->get();

        // Get saved payment methods
        $paymentMethods = $user->paymentMethods()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'subtotal' => $subtotal,
                'shipping_cost' => 0,
                'discount' => 0,
                'tax' => $subtotal * 0.1,
                'total' => $subtotal + ($subtotal * 0.1),
                'addresses' => $addresses,
                'default_address' => $defaultAddress,
                'shipping_methods' => $shippingMethods,
                'payment_methods' => $paymentMethods,
                'currency' => 'USD'
            ]
        ]);
    }

    // Process checkout
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_address_id' => 'required|exists:addresses,id',
            'billing_address_id' => 'nullable|exists:addresses,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'coupon_code' => 'nullable|string|exists:coupons,code',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Get cart
        $cart = $user->cart()->with(['items.variant.watch'])->first();

        if (!$cart || $cart->items->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        // Check stock availability
        foreach ($cart->items as $item) {
            if ($item->variant->stock < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock for: ' . $item->variant->watch->model,
                    'data' => [
                        'variant_id' => $item->variant_id,
                        'available' => $item->variant->stock,
                        'requested' => $item->quantity
                    ]
                ], 400);
            }
        }

        // Validate shipping address belongs to user
        $shippingAddress = Address::where('id', $request->shipping_address_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$shippingAddress) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping address not found'
            ], 404);
        }

        // Billing address (use shipping if not provided)
        $billingAddressId = $request->billing_address_id ?? $request->shipping_address_id;
        $billingAddress = Address::where('id', $billingAddressId)
            ->where('user_id', $user->id)
            ->first();

        if (!$billingAddress) {
            return response()->json([
                'success' => false,
                'message' => 'Billing address not found'
            ], 404);
        }

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];

        foreach ($cart->items as $item) {
            $watch = $item->variant->watch;
            $price = ($watch->discounted_price ?? $watch->base_price) + $item->variant->additional_price;
            $total = $price * $item->quantity;
            $subtotal += $total;

            $orderItems[] = [
                'variant_id' => $item->variant_id,
                'watch_id' => $watch->id,
                'quantity' => $item->quantity,
                'price' => $price,
                'total' => $total
            ];
        }

        // Apply coupon if provided
        $discount = 0;
        $couponId = null;

        if ($request->coupon_code) {
            $coupon = Coupon::where('code', $request->coupon_code)
                ->where('is_active', true)
                ->where('starts_at', '<=', now())
                ->where('expires_at', '>=', now())
                ->first();

            if ($coupon) {
                // Check usage limit
                if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Coupon usage limit exceeded'
                    ], 400);
                }

                // Check per user limit
                $userUsage = $coupon->usages()->where('user_id', $user->id)->count();
                if ($coupon->per_user_limit && $userUsage >= $coupon->per_user_limit) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You have already used this coupon'
                    ], 400);
                }

                // Check minimum order amount
                if ($subtotal < $coupon->min_order_amount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Minimum order amount for this coupon is ' . $coupon->min_order_amount
                    ], 400);
                }

                // Calculate discount
                if ($coupon->type == 'percentage') {
                    $discount = ($subtotal * $coupon->value) / 100;
                    if ($coupon->max_discount && $discount > $coupon->max_discount) {
                        $discount = $coupon->max_discount;
                    }
                } else {
                    $discount = $coupon->value;
                }

                $couponId = $coupon->id;
            }
        }

        // Get shipping method
        $shippingMethod = ShippingMethod::find($request->shipping_method_id);
        $shippingCost = 0;

        if ($shippingMethod) {
            // Calculate shipping cost based on country
            $shippingRate = $shippingMethod->rates()
                ->where('country_code', $shippingAddress->country)
                ->where('min_order_amount', '<=', $subtotal)
                ->orderBy('min_order_amount', 'desc')
                ->first();

            if ($shippingRate) {
                $shippingCost = $shippingRate->is_free_shipping ? 0 : $shippingRate->rate;
            } else {
                // Default rate if no specific rate found
                $shippingCost = 10;
            }
        }

        // Calculate tax
        $tax = ($subtotal - $discount) * 0.1;
        $total = $subtotal - $discount + $shippingCost + $tax;

        // Start transaction
        DB::beginTransaction();

        try {
            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $user->id,
                'status' => 'pending',
                'payment_status' => 'pending',
                'total_amount' => $total,
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_cost' => $shippingCost,
                'tax_amount' => $tax,
                'currency_code' => 'USD',
                'shipping_address_id' => $shippingAddress->id,
                'billing_address_id' => $billingAddress->id,
                'shipping_method_id' => $request->shipping_method_id,
                'coupon_id' => $couponId,
                'payment_method_id' => $request->payment_method_id,
                'notes' => $request->notes,
                'placed_at' => now(),
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $item['variant_id'],
                    'watch_id' => $item['watch_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'discount_applied' => 0,
                    'total' => $item['total'],
                ]);

                // Reduce stock
                $variant = WatchVariant::find($item['variant_id']);
                $variant->decrement('stock', $item['quantity']);
            }

            // Update coupon usage
            if ($couponId) {
                $coupon->increment('used_count');
                $coupon->usages()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'used_at' => now(),
                ]);
            }

            // Clear cart
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order' => $order,
                    'order_number' => $order->order_number,
                    'total' => $total,
                    'currency' => 'USD'
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage()
            ], 500);
        }
    }

    // Confirm order (after payment)
    public function confirm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_status' => 'required|in:paid,failed',
            'transaction_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        if ($order->payment_status != 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Order payment already processed'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Update order
            $order->update([
                'payment_status' => $request->payment_status,
                'status' => $request->payment_status == 'paid' ? 'processing' : 'cancelled'
            ]);

            // Create payment record
            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $order->payment_method_id,
                'transaction_id' => $request->transaction_id,
                'amount' => $order->total_amount,
                'currency' => $order->currency_code,
                'status' => $request->payment_status == 'paid' ? 'completed' : 'failed',
                'payment_data' => json_encode([
                    'confirmed_at' => now(),
                    'status' => $request->payment_status
                ])
            ]);

            // If payment failed, restore stock
            if ($request->payment_status == 'failed') {
                foreach ($order->items as $item) {
                    $variant = WatchVariant::find($item->variant_id);
                    $variant->increment('stock', $item->quantity);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order ' . ($request->payment_status == 'paid' ? 'confirmed' : 'failed') . ' successfully',
                'data' => [
                    'order' => $order,
                    'status' => $order->status
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm order: ' . $e->getMessage()
            ], 500);
        }
    }
}
