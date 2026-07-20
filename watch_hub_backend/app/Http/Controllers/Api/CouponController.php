<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    // Validate coupon code
    public function validateCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|exists:coupons,code',
            'subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found or inactive'
            ], 404);
        }

        // Check if coupon is valid
        $validation = $this->validateCouponRules($coupon, $request->user(), $request->subtotal);

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['message']
            ], 400);
        }

        // Calculate discount
        $discount = $this->calculateDiscount($coupon, $request->subtotal);

        return response()->json([
            'success' => true,
            'message' => 'Coupon is valid',
            'data' => [
                'coupon' => $coupon,
                'discount' => $discount,
                'type' => $coupon->type,
                'code' => $coupon->code,
                'min_order_amount' => $coupon->min_order_amount,
                'expires_at' => $coupon->expires_at,
            ]
        ]);
    }

    // Get all available coupons
    public function getAvailableCoupons(Request $request)
    {
        $now = now();

        $coupons = Coupon::where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('expires_at', '>=', $now)
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                    ->orWhereRaw('used_count < usage_limit');
            })
            ->orderBy('value', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }

    // Get coupon usage history
    public function getUsageHistory(Request $request)
    {
        $usages = CouponUsage::where('user_id', $request->user()->id)
            ->with('coupon', 'order')
            ->orderBy('used_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $usages
        ]);
    }

    // Get coupon details by code
    public function getByCode(Request $request, $code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $coupon
        ]);
    }

    // Helper: Validate coupon rules
    private function validateCouponRules($coupon, $user, $subtotal)
    {
        // Check if coupon is active
        if (!$coupon->is_active) {
            return ['valid' => false, 'message' => 'Coupon is not active'];
        }

        // Check date range
        $now = now();
        if ($coupon->starts_at > $now) {
            return ['valid' => false, 'message' => 'Coupon has not started yet'];
        }
        if ($coupon->expires_at < $now) {
            return ['valid' => false, 'message' => 'Coupon has expired'];
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Coupon usage limit exceeded'];
        }

        // Check per user limit
        if ($coupon->per_user_limit) {
            $userUsage = $coupon->usages()->where('user_id', $user->id)->count();
            if ($userUsage >= $coupon->per_user_limit) {
                return ['valid' => false, 'message' => 'You have already used this coupon'];
            }
        }

        // Check minimum order amount
        if ($subtotal < $coupon->min_order_amount) {
            return ['valid' => false, 'message' => 'Minimum order amount is ' . $coupon->min_order_amount];
        }

        return ['valid' => true, 'message' => 'Coupon is valid'];
    }

    // Helper: Calculate discount
    private function calculateDiscount($coupon, $subtotal)
    {
        if ($coupon->type === 'percentage') {
            $discount = ($subtotal * $coupon->value) / 100;
            if ($coupon->max_discount && $discount > $coupon->max_discount) {
                $discount = $coupon->max_discount;
            }
        } else {
            $discount = $coupon->value;
        }

        return min($discount, $subtotal); // Cannot discount more than subtotal
    }
}
