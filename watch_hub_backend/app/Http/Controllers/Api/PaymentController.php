<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // Process payment for an order
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'payment_data' => 'nullable|array',
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

        // Check if order already paid
        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Order already paid'
            ], 400);
        }

        $paymentMethod = PaymentMethod::where('id', $request->payment_method_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        // Process payment based on provider
        $result = $this->processPayment($order, $paymentMethod, $request->payment_data);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'data' => $result['data']
        ]);
    }

    // Payment callback (webhook)
    public function callback(Request $request)
    {
        // This is where payment gateway webhooks would be handled
        // For now, we'll just log the callback and update order status

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'status' => 'required|in:completed,failed,pending',
            'order_id' => 'required|exists:orders,id',
            'payment_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Find or create payment record
        $payment = Payment::where('transaction_id', $request->transaction_id)->first();

        if (!$payment) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $request->transaction_id,
                'amount' => $order->total_amount,
                'currency' => $order->currency_code,
                'status' => $request->status,
                'payment_data' => $request->payment_data
            ]);
        } else {
            $payment->update([
                'status' => $request->status,
                'payment_data' => array_merge(
                    $payment->payment_data ?? [],
                    $request->payment_data ?? []
                )
            ]);
        }

        // Update order status based on payment
        if ($request->status === 'completed') {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing'
            ]);
        } elseif ($request->status === 'failed') {
            $order->update([
                'payment_status' => 'failed',
                'status' => 'cancelled'
            ]);

            // Restore stock
            foreach ($order->items as $item) {
                $variant = $item->variant;
                $variant->increment('stock', $item->quantity);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Callback processed successfully'
        ]);
    }

    // Verify payment
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $payment = Payment::where('transaction_id', $request->transaction_id)
            ->whereHas('order', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        // Here you would verify with payment provider
        // For demo, we'll return the payment status

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $payment,
                'status' => $payment->status,
                'order_status' => $payment->order->status
            ]
        ]);
    }

    // Get payment history
    public function history(Request $request)
    {
        $payments = Payment::whereHas('order', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    // Get payment status for an order
    public function getStatus($orderId, Request $request)
    {
        $order = Order::where('id', $orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $payment = $order->payments()->latest()->first();

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'order_status' => $order->status,
                'payment' => $payment,
                'total_amount' => $order->total_amount,
                'currency' => $order->currency_code
            ]
        ]);
    }

    // Process refund
    public function refund(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
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

        // Check if order can be refunded
        if ($order->status !== 'delivered') {
            return response()->json([
                'success' => false,
                'message' => 'Only delivered orders can be refunded'
            ], 400);
        }

        if ($order->payment_status !== 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Only paid orders can be refunded'
            ], 400);
        }

        $amount = $request->amount ?? $order->total_amount;

        if ($amount > $order->total_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Refund amount cannot exceed order total'
            ], 400);
        }

        // Process refund (integrate with payment gateway)
        // For demo, we'll update the order status

        $order->update([
            'status' => 'refunded',
            'payment_status' => 'refunded'
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            $variant = $item->variant;
            $variant->increment('stock', $item->quantity);
        }

        // Create refund payment record
        Payment::create([
            'order_id' => $order->id,
            'amount' => -$amount,
            'currency' => $order->currency_code,
            'status' => 'refunded',
            'payment_data' => [
                'refund_reason' => $request->reason ?? 'Customer requested refund',
                'refunded_at' => now()
            ]
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Refund processed successfully',
            'data' => [
                'order' => $order,
                'refunded_amount' => $amount
            ]
        ]);
    }

    // Get available payment providers
    public function getProviders()
    {
        $providers = [
            [
                'name' => 'Stripe',
                'value' => 'stripe',
                'logo' => '/images/payments/stripe.png',
                'enabled' => true,
                'supported_currencies' => ['USD', 'EUR', 'GBP']
            ],
            [
                'name' => 'PayPal',
                'value' => 'paypal',
                'logo' => '/images/payments/paypal.png',
                'enabled' => true,
                'supported_currencies' => ['USD', 'EUR', 'GBP']
            ],
            [
                'name' => 'Razorpay',
                'value' => 'razorpay',
                'logo' => '/images/payments/razorpay.png',
                'enabled' => true,
                'supported_currencies' => ['INR']
            ],
            [
                'name' => 'Paystack',
                'value' => 'paystack',
                'logo' => '/images/payments/paystack.png',
                'enabled' => true,
                'supported_currencies' => ['NGN']
            ],
            [
                'name' => 'Cash on Delivery',
                'value' => 'cod',
                'logo' => '/images/payments/cod.png',
                'enabled' => true,
                'supported_currencies' => ['ALL']
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }

    // Helper: Process payment (simulated)
    private function processPayment($order, $paymentMethod, $paymentData = null)
    {
        // In real implementation, this would call payment gateway API
        // For demo, we'll simulate success

        try {
            $transactionId = 'txn_' . strtoupper(substr(md5(rand()), 0, 16));

            // Create payment record
            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => $paymentMethod->id,
                'transaction_id' => $transactionId,
                'amount' => $order->total_amount,
                'currency' => $order->currency_code,
                'status' => 'completed',
                'payment_data' => array_merge(
                    ['provider' => $paymentMethod->provider],
                    $paymentData ?? []
                )
            ]);

            // Update order
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_method_id' => $paymentMethod->id
            ]);

            return [
                'success' => true,
                'data' => [
                    'payment' => $payment,
                    'transaction_id' => $transactionId,
                    'order' => $order
                ]
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage()
            ];
        }
    }
}
