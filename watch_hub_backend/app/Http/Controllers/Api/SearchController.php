<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // Global search across watches, brands, categories
    public function search(Request $request)
    {
        $query = $request->query('q');
        $limit = $request->limit ?? 10;

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 422);
        }

        // Search watches
        $watches = Watch::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('model', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with(['brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->limit($limit)
            ->get();

        // Search brands
        $brands = Brand::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get();

        // Search categories
        $categories = Category::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'watches' => $watches,
                'brands' => $brands,
                'categories' => $categories
            ]
        ]);
    }

    // Search only watches
    public function searchWatches(Request $request)
    {
        $query = $request->query('q');
        $limit = $request->limit ?? 15;

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 422);
        }

        $watches = Watch::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('model', 'like', '%' . $query . '%')
                    ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with(['brand', 'category', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Advanced search with filters
    public function advancedSearch(Request $request)
    {
        $query = Watch::where('is_active', true);

        // Search term
        if ($request->has('q') && !empty($request->q)) {
            $query->where(function ($q) use ($request) {
                $q->where('model', 'like', '%' . $request->q . '%')
                    ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        // Filter by brand
        if ($request->has('brand_ids') && !empty($request->brand_ids)) {
            $brandIds = explode(',', $request->brand_ids);
            $query->whereIn('brand_id', $brandIds);
        }

        // Filter by category
        if ($request->has('category_ids') && !empty($request->category_ids)) {
            $categoryIds = explode(',', $request->category_ids);
            $query->whereIn('category_id', $categoryIds);
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

        // Filter by discount
        if ($request->has('on_sale') && $request->on_sale) {
            $query->where('discount_percent', '>', 0);
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

    // Autocomplete suggestions
    public function autocomplete(Request $request)
    {
        $query = $request->query('q');
        $limit = $request->limit ?? 5;

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 422);
        }

        // Watch suggestions
        $watchSuggestions = Watch::where('is_active', true)
            ->where('model', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get(['id', 'model', 'slug']);

        // Brand suggestions
        $brandSuggestions = Brand::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get(['id', 'name']);

        // Category suggestions
        $categorySuggestions = Category::where('is_active', true)
            ->where('name', 'like', '%' . $query . '%')
            ->limit($limit)
            ->get(['id', 'name', 'slug']);

        return response()->json([
            'success' => true,
            'data' => [
                'watches' => $watchSuggestions,
                'brands' => $brandSuggestions,
                'categories' => $categorySuggestions
            ]
        ]);
    }

    // Get filter options (brands, categories, price range, ratings)
    public function filterOptions(Request $request)
    {
        // Get all active brands with watch count
        $brands = Brand::where('is_active', true)
            ->withCount('watches')
            ->having('watches_count', '>', 0)
            ->orderBy('name')
            ->get();

        // Get all active categories with watch count
        $categories = Category::where('is_active', true)
            ->withCount('watches')
            ->having('watches_count', '>', 0)
            ->orderBy('name')
            ->get();

        // Get price range
        $minPrice = Watch::where('is_active', true)->min('discounted_price') ?? 0;
        $maxPrice = Watch::where('is_active', true)->max('discounted_price') ?? 1000;

        // Get rating options
        $ratings = [
            ['value' => 4, 'label' => '4★ & above'],
            ['value' => 3, 'label' => '3★ & above'],
            ['value' => 2, 'label' => '2★ & above'],
            ['value' => 1, 'label' => '1★ & above']
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'brands' => $brands,
                'categories' => $categories,
                'price_range' => [
                    'min' => (float) $minPrice,
                    'max' => (float) $maxPrice
                ],
                'ratings' => $ratings,
                'sort_options' => [
                    ['value' => 'created_at_desc', 'label' => 'Newest'],
                    ['value' => 'created_at_asc', 'label' => 'Oldest'],
                    ['value' => 'price_asc', 'label' => 'Price: Low to High'],
                    ['value' => 'price_desc', 'label' => 'Price: High to Low'],
                    ['value' => 'rating_desc', 'label' => 'Top Rated'],
                    ['value' => 'popularity_desc', 'label' => 'Popularity']
                ]
            ]
        ]);
    }

    // Get recent searches (trending)
    public function trendingSearches()
    {
        // In production, you would track search logs
        // For now, return some static trending searches
        $trending = [
            'Rolex',
            'Smartwatch',
            'Luxury',
            'G-Shock',
            'Diver',
            'Chronograph',
            'Seiko',
            'Analog'
        ];

        return response()->json([
            'success' => true,
            'data' => $trending
        ]);
    }
}
