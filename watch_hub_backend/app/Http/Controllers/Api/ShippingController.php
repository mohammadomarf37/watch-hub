<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    // Get all shipping methods
    public function getMethods(Request $request)
    {
        $query = ShippingMethod::where('is_active', true);

        // Get shipping methods with rates for user's country
        $shippingMethods = $query->with(['rates' => function ($q) use ($request) {
            if ($request->has('country_code')) {
                $q->where('country_code', $request->country_code);
            }
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $shippingMethods
        ]);
    }

    // Calculate shipping cost
    public function calculateRate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'shipping_method_id' => 'required|exists:shipping_methods,id',
            'country_code' => 'required|string|size:2',
            'subtotal' => 'required|numeric|min:0',
            'address_id' => 'nullable|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $shippingMethod = ShippingMethod::find($request->shipping_method_id);

        if (!$shippingMethod || !$shippingMethod->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping method not available'
            ], 404);
        }

        // Get shipping rate
        $rate = $this->getShippingRate(
            $shippingMethod,
            $request->country_code,
            $request->subtotal
        );

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping not available for this country'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'shipping_method' => $shippingMethod,
                'rate' => $rate,
                'cost' => $rate->is_free_shipping ? 0 : $rate->rate,
                'is_free_shipping' => $rate->is_free_shipping,
                'delivery_days' => [
                    'min' => $shippingMethod->delivery_days_min,
                    'max' => $shippingMethod->delivery_days_max
                ]
            ]
        ]);
    }

    // Get shipping rates for all methods
    public function getAllRates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_code' => 'required|string|size:2',
            'subtotal' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $shippingMethods = ShippingMethod::where('is_active', true)->get();
        $rates = [];

        foreach ($shippingMethods as $method) {
            $rate = $this->getShippingRate(
                $method,
                $request->country_code,
                $request->subtotal
            );

            if ($rate) {
                $rates[] = [
                    'shipping_method' => $method,
                    'rate' => $rate,
                    'cost' => $rate->is_free_shipping ? 0 : $rate->rate,
                    'is_free_shipping' => $rate->is_free_shipping,
                    'delivery_days' => [
                        'min' => $method->delivery_days_min,
                        'max' => $method->delivery_days_max
                    ]
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $rates
        ]);
    }

    // Get shipping rate by address
    public function getRateByAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
            'shipping_method_id' => 'required|exists:shipping_methods,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $address = Address::where('id', $request->address_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        // Get cart subtotal
        $cart = $request->user()->cart()->with('items.variant.watch')->first();
        $subtotal = 0;
        if ($cart) {
            $subtotal = $cart->items->sum(function ($item) {
                $watch = $item->variant->watch;
                $price = ($watch->discounted_price ?? $watch->base_price) + $item->variant->additional_price;
                return $price * $item->quantity;
            });
        }

        $shippingMethod = ShippingMethod::find($request->shipping_method_id);
        $rate = $this->getShippingRate(
            $shippingMethod,
            $address->country,
            $subtotal
        );

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Shipping not available for this country'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'address' => $address,
                'shipping_method' => $shippingMethod,
                'rate' => $rate,
                'cost' => $rate->is_free_shipping ? 0 : $rate->rate,
                'is_free_shipping' => $rate->is_free_shipping,
                'subtotal' => $subtotal,
                'delivery_days' => [
                    'min' => $shippingMethod->delivery_days_min,
                    'max' => $shippingMethod->delivery_days_max
                ]
            ]
        ]);
    }

    // Get shipping countries
    public function getCountries()
    {
        $countries = ShippingRate::select('country_code')
            ->distinct()
            ->orderBy('country_code')
            ->get()
            ->map(function ($item) {
                return [
                    'code' => $item->country_code,
                    'name' => $this->getCountryName($item->country_code)
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $countries
        ]);
    }

    // Helper: Get shipping rate
    private function getShippingRate($shippingMethod, $countryCode, $subtotal)
    {
        return ShippingRate::where('shipping_method_id', $shippingMethod->id)
            ->where('country_code', $countryCode)
            ->where('min_order_amount', '<=', $subtotal)
            ->orderBy('min_order_amount', 'desc')
            ->first();
    }

    // Helper: Get country name from code
    private function getCountryName($code)
    {
        $countries = [
            'US' => 'United States',
            'PK' => 'Pakistan',
            'UK' => 'United Kingdom',
            'GB' => 'United Kingdom',
            'EU' => 'Europe',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'IN' => 'India',
            'AE' => 'UAE',
            'SA' => 'Saudi Arabia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'CH' => 'Switzerland',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'DK' => 'Denmark',
            'FI' => 'Finland',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'SG' => 'Singapore',
            'MY' => 'Malaysia',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'PH' => 'Philippines',
        ];

        return $countries[$code] ?? $code;
    }
}
