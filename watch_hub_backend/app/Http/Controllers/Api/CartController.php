<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\WatchVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // Get user cart with items
    public function index(Request $request)
    {
        $cart = $request->user()->cart()->with(['items.variant.watch' => function ($q) {
            $q->with(['brand', 'images' => function ($img) {
                $img->where('is_primary', true);
            }]);
        }])->first();

        if (!$cart) {
            $cart = $request->user()->cart()->create();
        }

        $total = $cart->items->sum(function ($item) {
            $watch = $item->variant->watch;
            $price = ($watch->discounted_price ?? $watch->base_price) + $item->variant->additional_price;
            return $price * $item->quantity;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'cart' => $cart,
                'items_count' => $cart->items->sum('quantity'),
                'total_items' => $cart->items->count(),
                'subtotal' => $total,
                'currency' => 'USD'
            ]
        ]);
    }

    // Add item to cart
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'variant_id' => 'required|exists:watch_variants,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $cart = $user->cart()->first();

        if (!$cart) {
            $cart = $user->cart()->create();
        }

        $variant = WatchVariant::find($request->variant_id);

        // Check stock
        if ($variant->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available',
                'data' => [
                    'available_stock' => $variant->stock
                ]
            ], 400);
        }

        // Check if item already exists in cart
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $request->variant_id)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $request->quantity;

            if ($variant->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough stock available',
                    'data' => [
                        'available_stock' => $variant->stock,
                        'current_quantity' => $cartItem->quantity
                    ]
                ], 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity
            ]);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => $cartItem->load('variant.watch')
        ], 201);
    }

    // Update cart item quantity
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        // Check stock
        $variant = $cartItem->variant;
        if ($variant->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available',
                'data' => [
                    'available_stock' => $variant->stock
                ]
            ], 400);
        }

        $cartItem->update([
            'quantity' => $request->quantity
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'data' => $cartItem->load('variant.watch')
        ]);
    }

    // Remove item from cart
    public function remove(Request $request, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully'
        ]);
    }

    // Clear entire cart
    public function clear(Request $request)
    {
        $cart = $request->user()->cart()->first();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }

    // Get cart count
    public function count(Request $request)
    {
        $cart = $request->user()->cart()->with('items')->first();

        $count = 0;
        if ($cart) {
            $count = $cart->items->sum('quantity');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ]
        ]);
    }

    // Get cart subtotal
    public function subtotal(Request $request)
    {
        $cart = $request->user()->cart()->with('items.variant.watch')->first();

        $subtotal = 0;
        if ($cart) {
            $subtotal = $cart->items->sum(function ($item) {
                $watch = $item->variant->watch;
                $price = ($watch->discounted_price ?? $watch->base_price) + $item->variant->additional_price;
                return $price * $item->quantity;
            });
        }

        return response()->json([
            'success' => true,
            'data' => [
                'subtotal' => $subtotal,
                'currency' => 'USD'
            ]
        ]);
    }

    // Move item to wishlist
    public function moveToWishlist(Request $request, $id)
    {
        $cartItem = CartItem::where('id', $id)
            ->whereHas('cart', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->first();

        if (!$cartItem) {
            return response()->json([
                'success' => false,
                'message' => 'Cart item not found'
            ], 404);
        }

        $user = $request->user();
        $wishlist = $user->wishlist()->first();

        if (!$wishlist) {
            $wishlist = $user->wishlist()->create();
        }

        // Check if already in wishlist
        $exists = $wishlist->items()->where('variant_id', $cartItem->variant_id)->exists();

        if (!$exists) {
            $wishlist->items()->create([
                'variant_id' => $cartItem->variant_id
            ]);
        }

        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item moved to wishlist successfully'
        ]);
    }
}
