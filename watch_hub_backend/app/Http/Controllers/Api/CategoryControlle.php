<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Get all categories with subcategories
    public function index(Request $request)
    {
        $query = Category::where('is_active', true);

        // Get only parent categories
        if ($request->has('parent_only') && $request->parent_only) {
            $query->whereNull('parent_id');
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->with('children')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Get single category with subcategories and watches
    public function show($id)
    {
        $category = Category::with([
            'children',
            'watches' => function ($query) {
                $query->where('is_active', true)
                    ->with(['images' => function ($q) {
                        $q->where('is_primary', true);
                    }]);
            }
        ])->find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    // Get category by slug
    public function getBySlug($slug)
    {
        $category = Category::with([
            'children',
            'watches' => function ($query) {
                $query->where('is_active', true)
                    ->with(['images' => function ($q) {
                        $q->where('is_primary', true);
                    }]);
            }
        ])->where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    // Get watches by category
    public function getWatches($id, Request $request)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $query = $category->watches()->where('is_active', true);

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
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
                'category' => $category->name,
                'watches' => $watches
            ]
        ]);
    }

    // Get categories with their watch count
    public function getWithCount()
    {
        $categories = Category::where('is_active', true)
            ->withCount('watches')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Get main categories (parents only)
    public function getMainCategories()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->withCount('watches')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Get subcategories of a category
    public function getSubcategories($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $subcategories = $category->children()
            ->where('is_active', true)
            ->withCount('watches')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subcategories
        ]);
    }
}
