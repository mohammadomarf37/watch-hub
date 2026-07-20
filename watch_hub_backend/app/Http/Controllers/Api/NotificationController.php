<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    // Get all notifications for authenticated user
    public function index(Request $request)
    {
        $query = $request->user()->notifications();

        // Filter by read/unread
        if ($request->has('status')) {
            if ($request->status === 'read') {
                $query->whereNotNull('read_at');
            } elseif ($request->status === 'unread') {
                $query->whereNull('read_at');
            }
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        // Count unread
        $unreadCount = $request->user()->notifications()->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount
            ]
        ]);
    }

    // Get single notification
    public function show(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    // Mark notification as read
    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => $notification
        ]);
    }

    // Mark all notifications as read
    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->whereNull('read_at')->update([
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    // Delete notification
    public function destroy(Request $request, $id)
    {
        $notification = $request->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    // Delete all notifications
    public function deleteAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications deleted successfully'
        ]);
    }

    // Get notification preferences
    public function preferences(Request $request)
    {
        // In production, you would have a user_preferences table
        // For now, return default preferences
        $preferences = [
            'order_updates' => true,
            'promotions' => true,
            'wishlist_alerts' => true,
            'price_drops' => true,
            'support_messages' => true,
            'marketing_emails' => false,
            'push_notifications' => true,
        ];

        return response()->json([
            'success' => true,
            'data' => $preferences
        ]);
    }

    // Update notification preferences
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_updates' => 'sometimes|boolean',
            'promotions' => 'sometimes|boolean',
            'wishlist_alerts' => 'sometimes|boolean',
            'price_drops' => 'sometimes|boolean',
            'support_messages' => 'sometimes|boolean',
            'marketing_emails' => 'sometimes|boolean',
            'push_notifications' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // In production, save preferences to database
        // For now, just return success

        return response()->json([
            'success' => true,
            'message' => 'Preferences updated successfully',
            'data' => $request->all()
        ]);
    }

    // Get unread notification count
    public function unreadCount(Request $request)
    {
        $count = $request->user()->notifications()->whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $count
            ]
        ]);
    }

    // Create notification (for internal use)
    public function createNotification($userId, $type, $title, $message, $data = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    // Get notifications by type
    public function getByType(Request $request, $type)
    {
        $validTypes = ['order', 'promotion', 'wishlist', 'support'];

        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid notification type'
            ], 400);
        }

        $notifications = $request->user()->notifications()
            ->where('type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
}
