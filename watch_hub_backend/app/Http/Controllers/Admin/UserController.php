<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // Get all users with filters
    public function index(Request $request)
    {
        $query = User::where('role', 'customer');

        // Search by name or email
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('is_active', $request->status == 'active');
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $users = $query->withCount(['orders', 'reviews'])
            ->withSum(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // Get single user details
    public function show($id)
    {
        $user = User::with([
            'addresses',
            'orders' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(5);
            },
            'reviews' => function ($q) {
                $q->with('watch')->orderBy('created_at', 'desc')->limit(5);
            },
            'paymentMethods'
        ])->where('role', 'customer')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Get user stats
        $stats = [
            'total_orders' => $user->orders()->count(),
            'total_spent' => $user->orders()->where('payment_status', 'paid')->sum('total_amount'),
            'total_reviews' => $user->reviews()->count(),
            'total_addresses' => $user->addresses()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'stats' => $stats
            ]
        ]);
    }

    // Toggle user status (active/inactive)
    public function toggleStatus($id)
    {
        $user = User::where('role', 'customer')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully',
            'data' => [
                'is_active' => $user->is_active
            ]
        ]);
    }

    // Delete user
    public function destroy($id)
    {
        $user = User::where('role', 'customer')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Check if user has orders
        if ($user->orders()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete user with orders. Consider deactivating instead.'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    // Get user statistics
    public function stats()
    {
        $totalUsers = User::where('role', 'customer')->count();
        $activeUsers = User::where('role', 'customer')->where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;

        $todayUsers = User::whereDate('created_at', today())
            ->where('role', 'customer')
            ->count();

        $thisMonthUsers = User::whereMonth('created_at', now()->month)
            ->where('role', 'customer')
            ->count();

        $usersWithOrders = User::where('role', 'customer')
            ->whereHas('orders')
            ->count();

        $usersWithReviews = User::where('role', 'customer')
            ->whereHas('reviews')
            ->count();

        // Get top customers by order count
        $topCustomers = User::where('role', 'customer')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'email', 'orders_count']);

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $totalUsers,
                'active' => $activeUsers,
                'inactive' => $inactiveUsers,
                'today' => $todayUsers,
                'this_month' => $thisMonthUsers,
                'users_with_orders' => $usersWithOrders,
                'users_with_reviews' => $usersWithReviews,
                'top_customers' => $topCustomers,
            ]
        ]);
    }

    // Get user activity log
    public function activity($id)
    {
        $user = User::where('role', 'customer')->find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $activity = [
            'recent_orders' => $user->orders()
                ->with(['items.watch' => function ($q) {
                    $q->with('brand');
                }])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_reviews' => $user->reviews()
                ->with('watch')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'login_history' => [
                'last_login' => $user->last_login_at,
                'account_created' => $user->created_at,
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $activity
        ]);
    }

    // Bulk delete users
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if any user has orders
        $usersWithOrders = User::whereIn('id', $request->ids)
            ->whereHas('orders')
            ->count();

        if ($usersWithOrders > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Some users have orders and cannot be deleted. Consider deactivating them instead.'
            ], 400);
        }

        User::whereIn('id', $request->ids)->where('role', 'customer')->delete();

        return response()->json([
            'success' => true,
            'message' => 'Users deleted successfully'
        ]);
    }

    // Bulk update user status
    public function bulkUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $isActive = $request->status == 'active';

        User::whereIn('id', $request->ids)
            ->where('role', 'customer')
            ->update(['is_active' => $isActive]);

        return response()->json([
            'success' => true,
            'message' => 'User status updated successfully'
        ]);
    }
}
