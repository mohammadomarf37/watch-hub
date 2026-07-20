<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    // Get all brands
    public function index(Request $request)
    {
        $query = Brand::where('is_active', true);

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $brands = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    // Get single brand with watches
    public function show($id)
    {
        $brand = Brand::with(['watches' => function ($query) {
            $query->where('is_active', true)
                ->with(['images' => function ($q) {
                    $q->where('is_primary', true);
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

    // Get brand by slug
    public function getBySlug($slug)
    {
        $brand = Brand::with(['watches' => function ($query) {
            $query->where('is_active', true)
                ->with(['images' => function ($q) {
                    $q->where('is_primary', true);
                }]);
        }])->where('slug', $slug)->first();

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

    // Get watches by brand
    public function getWatches($id, Request $request)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found'
            ], 404);
        }

        $query = $brand->watches()->where('is_active', true);

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('discounted_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('discounted_price', '<=', $request->max_price);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $watches = $query->with(['images' => function ($q) {
            $q->where('is_primary', true);
        }])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => [
                'brand' => $brand->name,
                'watches' => $watches
            ]
        ]);
    }

    // Get top brands with most watches
    public function topBrands(Request $request)
    {
        $limit = $request->limit ?? 6;

        $brands = Brand::where('is_active', true)
            ->withCount('watches')
            ->having('watches_count', '>', 0)
            ->orderBy('watches_count', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }

    // Get brands with their watch count
    public function getWithCount()
    {
        $brands = Brand::where('is_active', true)
            ->withCount('watches')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $brands
        ]);
    }
}
