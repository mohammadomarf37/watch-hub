<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentMethodController extends Controller
{
    // Get all payment methods for authenticated user
    public function index(Request $request)
    {
        $paymentMethods = $request->user()->paymentMethods()
            ->orderBy('is_default', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    // Get single payment method
    public function show(Request $request, $id)
    {
        $paymentMethod = $request->user()->paymentMethods()->find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paymentMethod
        ]);
    }

    // Add new payment method
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|in:stripe,paypal,razorpay,paystack',
            'provider_customer_id' => 'required|string|max:255',
            'payment_method_id' => 'required|string|max:255',
            'last_four' => 'nullable|string|size:4',
            'brand' => 'nullable|string|max:50',
            'exp_month' => 'nullable|integer|min:1|max:12',
            'exp_year' => 'nullable|integer|min:2024|max:2035',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if payment method already exists
        $exists = $user->paymentMethods()
            ->where('provider_customer_id', $request->provider_customer_id)
            ->where('payment_method_id', $request->payment_method_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method already exists'
            ], 409);
        }

        // If setting as default, remove default from others
        if ($request->is_default) {
            $user->paymentMethods()->update(['is_default' => false]);
        }

        $paymentMethod = $user->paymentMethods()->create([
            'provider' => $request->provider,
            'provider_customer_id' => $request->provider_customer_id,
            'payment_method_id' => $request->payment_method_id,
            'last_four' => $request->last_four,
            'brand' => $request->brand,
            'exp_month' => $request->exp_month,
            'exp_year' => $request->exp_year,
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment method added successfully',
            'data' => $paymentMethod
        ], 201);
    }

    // Delete payment method
    public function destroy(Request $request, $id)
    {
        $paymentMethod = $request->user()->paymentMethods()->find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        // Check if payment method is used in orders
        $orderCount = $paymentMethod->orders()->count();

        if ($orderCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete payment method as it has associated orders'
            ], 400);
        }

        $paymentMethod->delete();

        // If deleted method was default, set another as default
        if ($paymentMethod->is_default) {
            $newDefault = $request->user()->paymentMethods()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully'
        ]);
    }

    // Set payment method as default
    public function setDefault(Request $request, $id)
    {
        $paymentMethod = $request->user()->paymentMethods()->find($id);

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method not found'
            ], 404);
        }

        // Remove default from all payment methods
        $request->user()->paymentMethods()->update(['is_default' => false]);

        // Set this as default
        $paymentMethod->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default payment method updated successfully',
            'data' => $paymentMethod->fresh()
        ]);
    }

    // Get default payment method
    public function getDefault(Request $request)
    {
        $paymentMethod = $request->user()->paymentMethods()
            ->where('is_default', true)
            ->first();

        if (!$paymentMethod) {
            return response()->json([
                'success' => false,
                'message' => 'No default payment method found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $paymentMethod
        ]);
    }

    // Get available providers
    public function getProviders()
    {
        $providers = [
            [
                'name' => 'Stripe',
                'value' => 'stripe',
                'logo' => '/images/payments/stripe.png',
                'enabled' => true
            ],
            [
                'name' => 'PayPal',
                'value' => 'paypal',
                'logo' => '/images/payments/paypal.png',
                'enabled' => true
            ],
            [
                'name' => 'Razorpay',
                'value' => 'razorpay',
                'logo' => '/images/payments/razorpay.png',
                'enabled' => true
            ],
            [
                'name' => 'Paystack',
                'value' => 'paystack',
                'logo' => '/images/payments/paystack.png',
                'enabled' => true
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $providers
        ]);
    }
}
