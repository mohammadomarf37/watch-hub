<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Watch;
use App\Models\WatchImage;
use App\Models\WatchPrice;
use App\Models\WatchSpecification;
use App\Models\WatchVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    // Get all products with filters
    public function index(Request $request)
    {
        $query = Watch::with(['brand', 'category', 'images' => function ($q) {
            $q->where('is_primary', true);
        }]);

        // Search
        if ($request->has('search')) {
            $query->where('model', 'like', '%' . $request->search . '%')
                ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by brand
        if ($request->has('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        // Filter by stock
        if ($request->has('stock_status')) {
            if ($request->stock_status == 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($request->stock_status == 'out_of_stock') {
                $query->where('stock', 0);
            } elseif ($request->stock_status == 'low_stock') {
                $query->where('stock', '>', 0)->where('stock', '<=', 5);
            }
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);
    }

    // Store new product
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'model' => 'required|string|max:200',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'required|integer|min:0',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'string|max:500',
            'variants' => 'nullable|array',
            'variants.*.color' => 'nullable|string',
            'variants.*.size' => 'nullable|string',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.additional_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = Str::slug($request->brand_id . ' ' . $request->model);

        // Check slug uniqueness
        $slugCount = Watch::where('slug', $slug)->count();
        if ($slugCount > 0) {
            $slug = $slug . '-' . ($slugCount + 1);
        }

        // Calculate discounted price
        $discountedPrice = null;
        if ($request->discount_percent && $request->discount_percent > 0) {
            $discountedPrice = $request->base_price * (1 - $request->discount_percent / 100);
        }

        $product = Watch::create([
            'brand_id' => $request->brand_id,
            'category_id' => $request->category_id,
            'model' => $request->model,
            'slug' => $slug,
            'description' => $request->description,
            'base_price' => $request->base_price,
            'discount_percent' => $request->discount_percent ?? 0,
            'discounted_price' => $discountedPrice,
            'stock' => $request->stock,
            'is_featured' => $request->is_featured ?? false,
            'is_active' => $request->is_active ?? true,
        ]);

        // Add specifications
        if ($request->has('specifications')) {
            foreach ($request->specifications as $spec) {
                WatchSpecification::create([
                    'watch_id' => $product->id,
                    'spec_key' => $spec['key'],
                    'spec_value' => $spec['value'],
                ]);
            }
        }

        // Add images
        if ($request->has('images')) {
            foreach ($request->images as $index => $image) {
                WatchImage::create([
                    'watch_id' => $product->id,
                    'image_url' => $image,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        // Add variants
        if ($request->has('variants')) {
            foreach ($request->variants as $variant) {
                WatchVariant::create([
                    'watch_id' => $product->id,
                    'color' => $variant['color'] ?? null,
                    'size' => $variant['size'] ?? null,
                    'stock' => $variant['stock'],
                    'additional_price' => $variant['additional_price'] ?? 0,
                    'sku' => strtoupper(Str::random(8)),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load(['brand', 'category', 'images', 'specifications', 'variants'])
        ], 201);
    }

    // Show single product
    public function show($id)
    {
        $product = Watch::with([
            'brand',
            'category',
            'images',
            'specifications',
            'variants',
            'prices.currency'
        ])->find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    // Update product
    public function update(Request $request, $id)
    {
        $product = Watch::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'brand_id' => 'sometimes|exists:brands,id',
            'category_id' => 'sometimes|exists:categories,id',
            'model' => 'sometimes|string|max:200',
            'description' => 'nullable|string',
            'base_price' => 'sometimes|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'stock' => 'sometimes|integer|min:0',
            'is_featured' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'specifications' => 'nullable|array',
            'images' => 'nullable|array',
            'variants' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update slug if model changed
        if ($request->has('model') && $request->model != $product->model) {
            $slug = Str::slug($request->brand_id . ' ' . $request->model);
            $slugCount = Watch::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($slugCount > 0) {
                $slug = $slug . '-' . ($slugCount + 1);
            }
            $request->merge(['slug' => $slug]);
        }

        // Calculate discounted price
        if ($request->has('base_price') || $request->has('discount_percent')) {
            $basePrice = $request->base_price ?? $product->base_price;
            $discountPercent = $request->discount_percent ?? $product->discount_percent;
            $request->merge([
                'discounted_price' => $discountPercent > 0 ? $basePrice * (1 - $discountPercent / 100) : null
            ]);
        }

        $product->update($request->only([
            'brand_id',
            'category_id',
            'model',
            'slug',
            'description',
            'base_price',
            'discount_percent',
            'discounted_price',
            'stock',
            'is_featured',
            'is_active'
        ]));

        // Update specifications
        if ($request->has('specifications')) {
            $product->specifications()->delete();
            foreach ($request->specifications as $spec) {
                WatchSpecification::create([
                    'watch_id' => $product->id,
                    'spec_key' => $spec['key'],
                    'spec_value' => $spec['value'],
                ]);
            }
        }

        // Update images
        if ($request->has('images')) {
            $product->images()->delete();
            foreach ($request->images as $index => $image) {
                WatchImage::create([
                    'watch_id' => $product->id,
                    'image_url' => $image,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        // Update variants
        if ($request->has('variants')) {
            $product->variants()->delete();
            foreach ($request->variants as $variant) {
                WatchVariant::create([
                    'watch_id' => $product->id,
                    'color' => $variant['color'] ?? null,
                    'size' => $variant['size'] ?? null,
                    'stock' => $variant['stock'],
                    'additional_price' => $variant['additional_price'] ?? 0,
                    'sku' => strtoupper(Str::random(8)),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load(['brand', 'category', 'images', 'specifications', 'variants'])
        ]);
    }

    // Delete product (soft delete)
    public function destroy($id)
    {
        $product = Watch::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ]);
    }

    // Toggle product status (active/inactive)
    public function toggleStatus($id)
    {
        $product = Watch::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product status updated successfully',
            'data' => [
                'is_active' => $product->is_active
            ]
        ]);
    }

    // Toggle featured status
    public function toggleFeatured($id)
    {
        $product = Watch::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }

        $product->is_featured = !$product->is_featured;
        $product->save();

        return response()->json([
            'success' => true,
            'message' => 'Product featured status updated successfully',
            'data' => [
                'is_featured' => $product->is_featured
            ]
        ]);
    }

    // Bulk delete products
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:watches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        Watch::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Products deleted successfully'
        ]);
    }

    // Bulk update stock
    public function bulkUpdateStock(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:watches,id',
            'stock' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        Watch::whereIn('id', $request->ids)->update(['stock' => $request->stock]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully'
        ]);
    }
}
