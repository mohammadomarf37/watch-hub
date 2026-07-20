<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    // Get all brands with filters
    public function index(Request $request)
    {
        $query = Brand::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        $brands = $query->withCount('watches')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    // Store new brand
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150|unique:brands,name',
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $brand = Brand::create([
            'name' => $request->name,
            'logo' => $request->logo,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully',
            'data' => $brand
        ], 201);
    }

    // Show single brand
    public function show($id)
    {
        $brand = Brand::with(['watches' => function ($q) {
            $q->with(['category', 'images' => function ($img) {
                $img->where('is_primary', true);
            }]);
        }])->find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $brand
        ]);
    }

    // Update brand
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:150|unique:brands,name,' . $id,
            'logo' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $brand->update($request->only(['name', 'logo', 'description', 'is_active']));

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully',
            'data' => $brand->fresh()
        ]);
    }

    // Delete brand
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        // Check if brand has watches
        if ($brand->watches()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete brand with associated products. Consider deactivating instead.'
            ], 400);
        }

        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully'
        ]);
    }

    // Toggle brand status
    public function toggleStatus($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $brand->is_active = !$brand->is_active;
        $brand->save();

        return response()->json([
            'success' => true,
            'message' => 'Brand status updated successfully',
            'data' => [
                'is_active' => $brand->is_active
            ]
        ]);
    }

    // Bulk delete brands
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if any brand has watches
        $brandsWithWatches = Brand::whereIn('id', $request->ids)
            ->whereHas('watches')
            ->count();

        if ($brandsWithWatches > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some brands have associated products and cannot be deleted'
            ], 400);
        }

        Brand::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brands deleted successfully'
        ]);
    }

    // Bulk update brand status
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:brands,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $isActive = $request->status == 'active';
        Brand::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'Brand status updated successfully'
        ]);
    }

    // Get brand statistics
    public function stats()
    {
        $stats = [
            'total' => Brand::count(),
            'active' => Brand::where('is_active', true)->count(),
            'inactive' => Brand::where('is_active', false)->count(),
            'with_products' => Brand::whereHas('watches')->count(),
            'without_products' => Brand::whereDoesntHave('watches')->count(),
        ];

        // Get top brands by product count
        $topBrands = Brand::withCount('watches')
            ->having('watches_count', '>', 0)
            ->orderBy('watches_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'top_brands' => $topBrands
            ]
        ]);
    }

    // Get all brands (simple list)
    public function getList()
    {
        $brands = Brand::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }
}
