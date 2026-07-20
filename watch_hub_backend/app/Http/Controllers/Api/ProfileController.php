<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    // Get authenticated user profile with relations
    public function show(Request $request)
    {
        $user = $request->user()->load(['cart', 'wishlist', 'addresses']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    // Update user profile
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'profile_image' => 'sometimes|string|max:500',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user->fill($request->only(['name', 'phone', 'profile_image', 'email']));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh()
        ]);
    }

    // Update profile image
    public function updateProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();
        $user->profile_image = $request->profile_image;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully',
            'data' => ['profile_image' => $user->profile_image]
        ]);
    }

    // Change password
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    // Delete account (soft delete)
    public function deleteAccount(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully'
        ]);
    }

    // Get user statistics
    public function stats(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'orders_count' => $user->orders()->count(),
                'wishlist_count' => $user->wishlist ? $user->wishlist->items()->count() : 0,
                'reviews_count' => $user->reviews()->count(),
                'addresses_count' => $user->addresses()->count(),
            ]
        ]);
    }

    // Get recent activity
    public function activity(Request $request)
    {
        $user = $request->user();

        $recentOrders = $user->orders()
            ->with(['items.watch', 'items.variant'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentReviews = $user->reviews()
            ->with('watch')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recent_orders' => $recentOrders,
                'recent_reviews' => $recentReviews,
            ]
        ]);
    }

    // Get notification preferences
    public function notificationPreferences(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'order_updates' => true,
                'promotions' => true,
                'wishlist_alerts' => true,
                'support_messages' => true,
            ]
        ]);
    }

    // Update notification preferences
    public function updateNotificationPreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_updates' => 'sometimes|boolean',
            'promotions' => 'sometimes|boolean',
            'wishlist_alerts' => 'sometimes|boolean',
            'support_messages' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification preferences updated successfully'
        ]);
    }
}
