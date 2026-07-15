<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home(): JsonResponse
    {
        return response()->json([
            'featured' => ProductResource::collection(
                Product::active()->featured()->with(['brand', 'category', 'images'])->take(8)->get()
            ),
            'new_arrivals' => ProductResource::collection(
                Product::active()->newArrivals()->with(['brand', 'category', 'images'])->take(8)->get()
            ),
            'brands' => BrandResource::collection(Brand::where('is_active', true)->orderBy('name')->get()),
            'categories' => CategoryResource::collection(Category::where('is_active', true)->orderBy('sort_order')->get()),
        ]);
    }

    public function products(Request $request)
    {
        $query = Product::active()->with(['brand', 'category', 'images']);

        if ($request->filled('search')) {
            $query->search($request->string('search')->toString());
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->integer('brand_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('min_price')) {
            $query->whereRaw('COALESCE(sale_price, price) >= ?', [$request->float('min_price')]);
        }

        if ($request->filled('max_price')) {
            $query->whereRaw('COALESCE(sale_price, price) <= ?', [$request->float('max_price')]);
        }

        match ($request->input('sort', 'popular')) {
            'price_low' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price_high' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'rating' => $query->orderByDesc('average_rating'),
            'newest' => $query->latest(),
            default => $query->orderByDesc('review_count')->orderByDesc('average_rating'),
        };

        return ProductResource::collection($query->paginate($request->integer('per_page', 20)));
    }

    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product->load([
            'brand',
            'category',
            'images',
            'reviews.user',
        ]));
    }

    public function brands()
    {
        return BrandResource::collection(Brand::where('is_active', true)->orderBy('name')->get());
    }

    public function categories()
    {
        return CategoryResource::collection(Category::where('is_active', true)->orderBy('sort_order')->get());
    }

    public function faqs(): JsonResponse
    {
        return response()->json([
            'data' => Faq::where('is_active', true)
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get(['id', 'question', 'answer', 'category']),
        ]);
    }
}
