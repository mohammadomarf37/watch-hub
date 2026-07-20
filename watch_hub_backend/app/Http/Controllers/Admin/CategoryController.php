<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    // Get all categories with filters
    public function index(Request $request)
    {
        $query = Category::query();

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        // Filter by parent
        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        $categories = $query->with(['parent', 'children' => function ($q) {
            $q->where('is_active', true);
        }])
            ->withCount('watches')
            ->orderBy('name')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Store new category
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug
        $slug = Str::slug($request->name);
        $slugCount = Category::where('slug', $slug)->count();
        if ($slugCount > 0) {
            $slug = $slug . '-' . ($slugCount + 1);
        }

        // Prevent self-parent
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            if ($parent && $parent->parent_id == $request->parent_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid parent category'
                ], 400);
            }
        }

        $category = Category::create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_id' => $request->parent_id,
            'icon' => $request->icon,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => $category->load('parent')
        ], 201);
    }

    // Show single category
    public function show($id)
    {
        $category = Category::with([
            'parent',
            'children' => function ($q) {
                $q->where('is_active', true);
            },
            'watches' => function ($q) {
                $q->where('is_active', true)->with(['brand', 'images' => function ($img) {
                    $img->where('is_primary', true);
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

    // Update category
    public function update(Request $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:150',
            'parent_id' => 'nullable|exists:categories,id|not_in:' . $id,
            'icon' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate slug if name changed
        $data = $request->only(['name', 'parent_id', 'icon', 'is_active']);

        if ($request->has('name') && $request->name != $category->name) {
            $slug = Str::slug($request->name);
            $slugCount = Category::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }
            $data['slug'] = $slug;
        }

        // Check for circular reference
        if ($request->parent_id) {
            $parent = Category::find($request->parent_id);
            if ($parent && $parent->parent_id == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Circular reference detected'
                ], 400);
            }
        }

        $category->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => $category->fresh()
        ]);
    }

    // Delete category
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        // Check if category has subcategories
        if ($category->children()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with subcategories'
            ], 400);
        }

        // Check if category has watches
        if ($category->watches()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with associated products'
            ], 400);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }

    // Toggle category status
    public function toggleStatus($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->is_active = !$category->is_active;
        $category->save();

        // If deactivating, also deactivate children
        if (!$category->is_active) {
            $category->children()->update(['is_active' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully',
            'data' => [
                'is_active' => $category->is_active,
                'children_updated' => true
            ]
        ]);
    }

    // Get category hierarchy (tree)
    public function getHierarchy()
    {
        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Get categories with parent info
    public function getWithParents()
    {
        $categories = Category::where('is_active', true)
            ->with(['parent' => function ($q) {
                $q->select('id', 'name');
            }])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    // Bulk delete categories
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if any category has children or watches
        $hasChildren = Category::whereIn('id', $request->ids)
            ->whereHas('children')
            ->count();

        $hasProducts = Category::whereIn('id', $request->ids)
            ->whereHas('watches')
            ->count();

        if ($hasChildren > 0 || $hasProducts > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some categories have subcategories or products and cannot be deleted'
            ], 400);
        }

        Category::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Categories deleted successfully'
        ]);
    }

    // Bulk update category status
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
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
        Category::whereIn('id', $request->ids)->update(['is_active' => $isActive]);

        // If deactivating, also deactivate children
        if (!$isActive) {
            Category::whereIn('parent_id', $request->ids)->update(['is_active' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Category status updated successfully'
        ]);
    }

    // Get category statistics
    public function stats()
    {
        $stats = [
            'total' => Category::count(),
            'active' => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'parent_categories' => Category::whereNull('parent_id')->count(),
            'sub_categories' => Category::whereNotNull('parent_id')->count(),
            'with_products' => Category::whereHas('watches')->count(),
            'without_products' => Category::whereDoesntHave('watches')->count(),
        ];

        // Get top categories by product count
        $topCategories = Category::withCount('watches')
            ->having('watches_count', '>', 0)
            ->orderBy('watches_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'top_categories' => $topCategories
            ]
        ]);
    }

    // Get all categories (simple list)
    public function getList()
    {
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id']);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
