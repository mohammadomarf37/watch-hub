<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Review\StoreReviewRequest;
use App\Http\Requests\Support\StoreTicketRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\CartResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return new UserResource($request->user()->refresh());
    }

    public function cart(Request $request): JsonResponse
    {
        $items = $request->user()->cartItems()->with(['product.brand', 'product.category', 'product.images'])->get();

        return response()->json([
            'data' => CartResource::collection($items),
            'total' => round($items->sum->subtotal, 2),
        ]);
    }

    public function addToCart(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:20']]);

        abort_if($product->stock < 1, 422, 'This watch is out of stock.');

        $item = Cart::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);
        $item->quantity = min($product->stock, ($item->exists ? $item->quantity : 0) + ($data['quantity'] ?? 1));
        $item->save();

        return $this->cart($request);
    }

    public function updateCart(Request $request, Cart $cart): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);

        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:20']]);
        $cart->update(['quantity' => min($cart->product->stock, $data['quantity'])]);

        return $this->cart($request);
    }

    public function removeCart(Request $request, Cart $cart): JsonResponse
    {
        abort_unless($cart->user_id === $request->user()->id, 403);
        $cart->delete();

        return $this->cart($request);
    }

    public function wishlist(Request $request)
    {
        return ProductResource::collection(
            $request->user()->wishlists()->with(['brand', 'category', 'images'])->get()
        );
    }

    public function toggleWishlist(Request $request, Product $product): JsonResponse
    {
        $result = $request->user()->wishlists()->toggle($product->id);

        return response()->json([
            'message' => count($result['attached']) ? 'Added to wishlist.' : 'Removed from wishlist.',
            'in_wishlist' => count($result['attached']) > 0,
        ]);
    }

    public function addresses(Request $request)
    {
        return AddressResource::collection($request->user()->addresses()->latest('is_default')->latest()->get());
    }

    public function storeAddress(StoreAddressRequest $request): AddressResource
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if ($data['is_default'] ?? false) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        return new AddressResource(Address::create($data));
    }

    public function orders(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()->with('items')->latest()->get()->map(fn (Order $order) => $this->orderPayload($order));

        return response()->json(['data' => $orders]);
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->validated('address_id'));
        $cartItems = $user->cartItems()->with(['product.images', 'product.brand', 'product.category'])->get();

        abort_if($cartItems->isEmpty(), 422, 'Your cart is empty.');

        $order = DB::transaction(function () use ($request, $user, $address, $cartItems) {
            $subtotal = round($cartItems->sum->subtotal, 2);
            $shipping = $subtotal >= 500 ? 0 : 25;
            $tax = round($subtotal * 0.05, 2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user->id,
                'address_id' => $address->id,
                'payment_method' => $request->validated('payment_method'),
                'payment_status' => $request->validated('payment_method') === 'cod' ? 'pending' : 'paid',
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'total' => $subtotal + $shipping + $tax,
                'shipping_address' => $address->toArray(),
                'notes' => $request->validated('notes'),
            ]);

            foreach ($cartItems as $item) {
                $product = $item->product;
                $price = $product->sale_price ?? $product->price;
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $price,
                    'quantity' => $item->quantity,
                    'subtotal' => $price * $item->quantity,
                    'product_snapshot' => (new ProductResource($product))->resolve(),
                ]);
                $product->decrement('stock', min($product->stock, $item->quantity));
            }

            $user->cartItems()->delete();

            return $order->load('items');
        });

        return response()->json(['data' => $this->orderPayload($order)], 201);
    }

    public function storeReview(StoreReviewRequest $request, Product $product): JsonResponse
    {
        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $product->id],
            [...$request->validated(), 'is_approved' => true]
        );
        $product->updateRating();

        return response()->json(['message' => 'Review saved.']);
    }

    public function supportTickets(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user()->supportTickets()->with('messages')->latest()->get(),
        ]);
    }

    public function storeTicket(StoreTicketRequest $request): JsonResponse
    {
        $ticket = $request->user()->supportTickets()->create($request->safe()->only(['subject', 'priority']));
        $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->validated('message'),
        ]);

        return response()->json(['data' => $ticket->load('messages')], 201);
    }

    private function orderPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'subtotal' => (float) $order->subtotal,
            'shipping_cost' => (float) $order->shipping_cost,
            'tax' => (float) $order->tax,
            'total' => (float) $order->total,
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'subtotal' => (float) $item->subtotal,
            ]),
            'created_at' => $order->created_at?->toISOString(),
        ];
    }
}
