<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use Illuminate\Http\Request;

class WatchController extends Controller
{
    // Get all watches with filters
    public function index(Request $request)
    {
        $query = Watch::where('is_active', true);

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

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

        // Filter by rating
        if ($request->has('min_rating')) {
            $query->where('rating', '>=', $request->min_rating);
        }

        // Filter by stock
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock', '>', 0);
        }

        // Search
        if ($request->has('search')) {
            $query->where('model', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $watches = $query->with(['brand', 'category', 'images' => function ($q) {
            $q->where('is_primary', true);
        }])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get single watch with all details
    public function show($id)
    {
        $watch = Watch::with([
            'brand',
            'category',
            'images',
            'variants',
            'specifications',
            'reviews' => function ($query) {
                $query->where('status', 'approved')->limit(5);
            }
        ])->find($id);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $watch
        ]);
    }

    // Get watch by slug
    public function getBySlug($slug)
    {
        $watch = Watch::with([
            'brand',
            'category',
            'images',
            'variants',
            'specifications',
            'reviews' => function ($query) {
                $query->where('status', 'approved')->limit(5);
            }
        ])->where('slug', $slug)->first();

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $watch
        ]);
    }

    // Get featured watches
    public function featured(Request $request)
    {
        $limit = $request->limit ?? 10;

        $watches = Watch::where('is_active', true)
            ->where('is_featured', true)
            ->with(['brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get new arrivals
    public function newArrivals(Request $request)
    {
        $limit = $request->limit ?? 10;

        $watches = Watch::where('is_active', true)
            ->with(['brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get recommended watches
    public function recommended(Request $request)
    {
        $limit = $request->limit ?? 10;
        $watchId = $request->watch_id;

        $query = Watch::where('is_active', true);

        // Exclude current watch if provided
        if ($watchId) {
            $query->where('id', '!=', $watchId);
        }

        // Get watches from same category or brand
        if ($watchId) {
            $watch = Watch::find($watchId);
            if ($watch) {
                $query->where(function ($q) use ($watch) {
                    $q->where('category_id', $watch->category_id)
                        ->orWhere('brand_id', $watch->brand_id);
                });
            }
        }

        $watches = $query->with(['brand', 'images' => function ($q) {
            $q->where('is_primary', true);
        }])
            ->inRandomOrder()
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get watches by brand
    public function getByBrand($brandId, Request $request)
    {
        $query = Watch::where('is_active', true)->where('brand_id', $brandId);

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $watches = $query->with(['images' => function ($q) {
            $q->where('is_primary', true);
        }])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get watches by category
    public function getByCategory($categoryId, Request $request)
    {
        $query = Watch::where('is_active', true)->where('category_id', $categoryId);

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $watches = $query->with(['images' => function ($q) {
            $q->where('is_primary', true);
        }])->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get watch reviews
    public function getReviews($id, Request $request)
    {
        $watch = Watch::find($id);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        $reviews = $watch->reviews()
            ->where('status', 'approved')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'watch' => $watch->model,
                'rating' => $watch->rating,
                'rating_count' => $watch->rating_count,
                'reviews' => $reviews
            ]
        ]);
    }

    // Check stock availability
    public function checkStock($id)
    {
        $watch = Watch::find($id);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'in_stock' => $watch->stock > 0,
                'stock' => $watch->stock,
                'message' => $watch->stock > 0 ? 'In stock' : 'Out of stock'
            ]
        ]);
    }
}
