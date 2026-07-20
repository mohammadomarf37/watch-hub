<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    // Get all addresses for authenticated user
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('is_default', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    // Get single address
    public function show(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    // Create new address
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'phone' => 'nullable|string|max:30',
            'address_type' => 'sometimes|in:shipping,billing',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // If setting as default, remove default from other addresses
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        $address = $user->addresses()->create([
            'address_line1' => $request->address_line1,
            'address_line2' => $request->address_line2,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'country' => $request->country,
            'phone' => $request->phone,
            'address_type' => $request->address_type ?? 'shipping',
            'is_default' => $request->is_default ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Address created successfully',
            'data' => $address
        ], 201);
    }

    // Update address
    public function update(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'address_line1' => 'sometimes|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:20',
            'country' => 'sometimes|string|max:100',
            'phone' => 'nullable|string|max:30',
            'address_type' => 'sometimes|in:shipping,billing',
            'is_default' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // If setting as default, remove default from other addresses
        if ($request->is_default) {
            $request->user()->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $address->update($request->only([
            'address_line1',
            'address_line2',
            'city',
            'state',
            'postal_code',
            'country',
            'phone',
            'address_type',
            'is_default'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Address updated successfully',
            'data' => $address->fresh()
        ]);
    }

    // Delete address
    public function destroy(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        // Check if address is used in orders
        $orderCount = $address->shippingOrders()->count() + $address->billingOrders()->count();

        if ($orderCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete address as it is associated with orders'
            ], 400);
        }

        $address->delete();

        // If deleted address was default, set another as default
        if ($address->is_default) {
            $newDefault = $request->user()->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    // Set address as default
    public function setDefault(Request $request, $id)
    {
        $address = $request->user()->addresses()->find($id);

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'Address not found'
            ], 404);
        }

        // Remove default from all addresses
        $request->user()->addresses()->update(['is_default' => false]);

        // Set this address as default
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Default address updated successfully',
            'data' => $address->fresh()
        ]);
    }

    // Get default address
    public function getDefault(Request $request)
    {
        $address = $request->user()->addresses()->where('is_default', true)->first();

        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'No default address found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $address
        ]);
    }

    // Get shipping addresses
    public function getShippingAddresses(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->where('address_type', 'shipping')
            ->orderBy('is_default', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }

    // Get billing addresses
    public function getBillingAddresses(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->where('address_type', 'billing')
            ->orderBy('is_default', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses
        ]);
    }
}
