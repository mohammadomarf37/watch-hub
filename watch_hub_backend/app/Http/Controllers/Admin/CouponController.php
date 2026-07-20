<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    // Get all coupons with filters
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Search by code
        if ($request->has('search')) {
            $query->where('code', 'like', '%' . $request->search . '%');
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $coupons
        ]);
    }

    // Store new coupon
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'applies_to' => 'sometimes|in:all,specific_products,specific_categories',
            'starts_at' => 'required|date',
            'expires_at' => 'required|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:watches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Convert code to uppercase
        $request->merge(['code' => strtoupper($request->code)]);

        $coupon = Coupon::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'min_order_amount' => $request->min_order_amount ?? 0,
            'max_discount' => $request->max_discount,
            'usage_limit' => $request->usage_limit,
            'per_user_limit' => $request->per_user_limit ?? 1,
            'applies_to' => $request->applies_to ?? 'all',
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'is_active' => $request->is_active ?? true,
        ]);

        // Add specific products if applicable
        if ($request->applies_to === 'specific_products' && $request->has('product_ids')) {
            foreach ($request->product_ids as $productId) {
                CouponProduct::create([
                    'coupon_id' => $coupon->id,
                    'watch_id' => $productId,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon created successfully',
            'data' => $coupon->load('products')
        ], 201);
    }

    // Show single coupon
    public function show($id)
    {
        $coupon = Coupon::with('products.watch')->find($id);

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

    // Update coupon
    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'applies_to' => 'sometimes|in:all,specific_products,specific_categories',
            'starts_at' => 'sometimes|date',
            'expires_at' => 'sometimes|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:watches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Convert code to uppercase
        if ($request->has('code')) {
            $request->merge(['code' => strtoupper($request->code)]);
        }

        $coupon->update($request->only([
            'code',
            'type',
            'value',
            'min_order_amount',
            'max_discount',
            'usage_limit',
            'per_user_limit',
            'applies_to',
            'starts_at',
            'expires_at',
            'is_active'
        ]));

        // Update specific products if applicable
        if ($request->has('applies_to') && $request->applies_to === 'specific_products') {
            $coupon->products()->delete();
            if ($request->has('product_ids')) {
                foreach ($request->product_ids as $productId) {
                    CouponProduct::create([
                        'coupon_id' => $coupon->id,
                        'watch_id' => $productId,
                    ]);
                }
            }
        } elseif ($request->has('applies_to') && $request->applies_to !== 'specific_products') {
            $coupon->products()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon updated successfully',
            'data' => $coupon->load('products')
        ]);
    }

    // Delete coupon
    public function destroy($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        $coupon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted successfully'
        ]);
    }

    // Toggle coupon status
    public function toggleStatus($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        $coupon->is_active = !$coupon->is_active;
        $coupon->save();

        return response()->json([
            'success' => true,
            'message' => 'Coupon status updated successfully',
            'data' => [
                'is_active' => $coupon->is_active
            ]
        ]);
    }

    // Get coupon usage report
    public function usageReport($id)
    {
        $coupon = Coupon::find($id);

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found'
            ], 404);
        }

        $usages = $coupon->usages()
            ->with(['user', 'order'])
            ->orderBy('used_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'coupon' => $coupon,
                'total_used' => $coupon->used_count,
                'remaining' => $coupon->usage_limit ? $coupon->usage_limit - $coupon->used_count : null,
                'usages' => $usages
            ]
        ]);
    }

    // Get coupon statistics
    public function stats()
    {
        $total = Coupon::count();
        $active = Coupon::where('is_active', true)->count();
        $inactive = $total - $active;

        $expired = Coupon::where('expires_at', '<', now())->count();
        $expiringSoon = Coupon::where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->addDays(7))
            ->count();

        $usageStats = [
            'total_used' => Coupon::sum('used_count'),
            'max_used' => Coupon::max('used_count'),
            'avg_usage' => Coupon::avg('used_count') ?? 0,
        ];

        $byType = [
            'percentage' => Coupon::where('type', 'percentage')->count(),
            'fixed' => Coupon::where('type', 'fixed')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'active' => $active,
                'inactive' => $inactive,
                'expired' => $expired,
                'expiring_soon' => $expiringSoon,
                'usage' => $usageStats,
                'by_type' => $byType,
            ]
        ]);
    }

    // Bulk delete coupons
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        Coupon::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coupons deleted successfully'
        ]);
    }

    // Bulk toggle coupon status
    public function bulkToggleStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:coupons,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $isActive = $request->status === 'active';
        Coupon::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon status updated successfully'
        ]);
    }

    // Get products that can be added to coupon
    public function getAvailableProducts(Request $request)
    {
        $query = Watch::where('is_active', true);

        if ($request->has('search')) {
            $query->where('model', 'like', '%' . $request->search . '%');
        }

        $products = $query->with(['brand', 'category'])
            ->orderBy('model')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }
}
