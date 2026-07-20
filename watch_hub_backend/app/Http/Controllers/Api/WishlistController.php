<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\WatchVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    // Get user wishlist with items
    public function index(Request $request)
    {
        $wishlist = $request->user()->wishlist()->with(['items.variant.watch' => function ($q) {
            $q->with(['brand', 'images' => function ($img) {
                $img->where('is_primary', true);
            }]);
        }])->first();

        if (!$wishlist) {
            $wishlist = $request->user()->wishlist()->create();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'wishlist' => $wishlist,
                'items_count' => $wishlist->items->count()
            ]
        ]);
    }

    // Add item to wishlist
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:watch_variants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $wishlist = $user->wishlist()->first();

        if (!$wishlist) {
            $wishlist = $user->wishlist()->create();
        }

        // Check if item already exists
        $exists = $wishlist->items()->where('variant_id', $request->variant_id)->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Item already in wishlist'
            ], 409);
        }

        $wishlistItem = $wishlist->items()->create([
            'variant_id' => $request->variant_id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item added to wishlist successfully',
            'data' => $wishlistItem->load('variant.watch')
        ], 201);
    }

    // Remove item from wishlist
    public function remove(Request $request, $id)
    {
        $wishlistItem = WishlistItem::where('id', $id)
            ->whereHas('wishlist', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $wishlistItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist successfully'
        ]);
    }

    // Remove item by variant_id
    public function removeByVariant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:watch_variants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $wishlistItem = WishlistItem::whereHas('wishlist', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->where('variant_id', $request->variant_id)->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found in wishlist'
            ], 404);
        }

        $wishlistItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from wishlist successfully'
        ]);
    }

    // Move item to cart
    public function moveToCart(Request $request, $id)
    {
        $wishlistItem = WishlistItem::where('id', $id)
            ->whereHas('wishlist', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->with('variant')
            ->first();

        if (!$wishlistItem) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist item not found'
            ], 404);
        }

        $variant = $wishlistItem->variant;

        // Check stock
        if ($variant->stock < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Item is out of stock',
                'data' => [
                    'variant_id' => $variant->id,
                    'stock' => $variant->stock
                ]
            ], 400);
        }

        $user = $request->user();
        $cart = $user->cart()->first();

        if (!$cart) {
            $cart = $user->cart()->create();
        }

        // Check if already in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variant->id)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + 1;

            if ($variant->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock',
                    'data' => [
                        'available_stock' => $variant->stock,
                        'current_quantity' => $cartItem->quantity
                    ]
                ], 400);
            }

            $cartItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id' => $cart->id,
                'variant_id' => $variant->id,
                'quantity' => 1,
            ]);
        }

        // Remove from wishlist
        $wishlistItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item moved to cart successfully'
        ]);
    }

    // Move all items to cart
    public function moveAllToCart(Request $request)
    {
        $user = $request->user();
        $wishlist = $user->wishlist()->with('items.variant')->first();

        if (!$wishlist || $wishlist->items->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Wishlist is empty'
            ], 400);
        }

        $cart = $user->cart()->first();

        if (!$cart) {
            $cart = $user->cart()->create();
        }

        $moved = 0;
        $failed = 0;
        $errors = [];

        foreach ($wishlist->items as $item) {
            $variant = $item->variant;

            // Check stock
            if ($variant->stock < 1) {
                $failed++;
                $errors[] = $variant->watch->model . ' is out of stock';
                continue;
            }

            // Check if already in cart
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('variant_id', $variant->id)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + 1;

                if ($variant->stock < $newQuantity) {
                    $failed++;
                    $errors[] = $variant->watch->model . ' - only ' . $variant->stock . ' available';
                    continue;
                }

                $cartItem->update(['quantity' => $newQuantity]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'variant_id' => $variant->id,
                    'quantity' => 1,
                ]);
            }

            $moved++;
        }

        // Clear wishlist
        $wishlist->items()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Items moved to cart successfully',
            'data' => [
                'moved' => $moved,
                'failed' => $failed,
                'errors' => $errors
            ]
        ]);
    }

    // Clear entire wishlist
    public function clear(Request $request)
    {
        $wishlist = $request->user()->wishlist()->first();

        if ($wishlist) {
            $wishlist->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared successfully'
        ]);
    }

    // Check if item is in wishlist
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:watch_variants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $wishlist = $request->user()->wishlist()->first();

        if (!$wishlist) {
            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => false
                ]
            ]);
        }

        $exists = $wishlist->items()->where('variant_id', $request->variant_id)->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $exists
            ]
        ]);
    }

    // Get wishlist count
    public function count(Request $request)
    {
        $wishlist = $request->user()->wishlist()->first();

        $count = 0;
        if ($wishlist) {
            $count = $wishlist->items()->count();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }
}
