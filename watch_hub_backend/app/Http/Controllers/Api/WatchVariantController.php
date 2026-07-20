<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WatchVariant;
use Illuminate\Http\Request;

class WatchVariantController extends Controller
{
    // Get all variants with optional filters
    public function index(Request $request)
    {
        $query = WatchVariant::with(['watch' => function ($q) {
            $q->where('is_active', true);
        }]);

        // Filter by watch
        if ($request->has('watch_id')) {
            $query->where('watch_id', $request->watch_id);
        }

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Filter by size
        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        // Filter by stock
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock', '>', 0);
        }

        $variants = $query->orderBy('additional_price')->get();

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    // Get single variant with watch details
    public function show($id)
    {
        $variant = WatchVariant::with(['watch' => function ($q) {
            $q->where('is_active', true)->with(['brand', 'category', 'images']);
        }])->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variant
        ]);
    }

    // Get variants by watch
    public function getByWatch($watchId, Request $request)
    {
        $query = WatchVariant::where('watch_id', $watchId);

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        // Filter by size
        if ($request->has('size')) {
            $query->where('size', $request->size);
        }

        // Filter by stock
        if ($request->has('in_stock') && $request->in_stock) {
            $query->where('stock', '>', 0);
        }

        $variants = $query->orderBy('additional_price')->get();

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }

    // Get available colors for a watch
    public function getColors($watchId)
    {
        $colors = WatchVariant::where('watch_id', $watchId)
            ->where('stock', '>', 0)
            ->select('color', 'color_hex')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $colors
        ]);
    }

    // Get available sizes for a watch
    public function getSizes($watchId, Request $request)
    {
        $query = WatchVariant::where('watch_id', $watchId)
            ->where('stock', '>', 0);

        // Filter by color
        if ($request->has('color')) {
            $query->where('color', $request->color);
        }

        $sizes = $query->select('size')->distinct()->pluck('size');

        return response()->json([
            'success' => true,
            'data' => $sizes
        ]);
    }

    // Get variant by SKU
    public function getBySku($sku)
    {
        $variant = WatchVariant::with(['watch' => function ($q) {
            $q->where('is_active', true)->with(['brand', 'category', 'images']);
        }])->where('sku', $sku)->first();

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $variant
        ]);
    }

    // Check variant stock
    public function checkStock($id)
    {
        $variant = WatchVariant::find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'in_stock' => $variant->stock > 0,
                'stock' => $variant->stock,
                'message' => $variant->stock > 0 ? 'In stock' : 'Out of stock'
            ]
        ]);
    }

    // Get variant price details
    public function getPrice($id)
    {
        $variant = WatchVariant::with('watch')->find($id);

        if (!$variant) {
            return response()->json([
                'success' => false,
                'message' => 'Variant not found'
            ], 404);
        }

        $watch = $variant->watch;
        $basePrice = $watch->discounted_price ?? $watch->base_price;
        $finalPrice = $basePrice + $variant->additional_price;

        return response()->json([
            'success' => true,
            'data' => [
                'base_price' => $basePrice,
                'additional_price' => $variant->additional_price,
                'final_price' => $finalPrice,
                'currency' => 'USD'
            ]
        ]);
    }

    // Get variants with low stock
    public function lowStock(Request $request)
    {
        $threshold = $request->threshold ?? 5;

        $variants = WatchVariant::with('watch')
            ->where('stock', '>', 0)
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $variants
        ]);
    }
}
