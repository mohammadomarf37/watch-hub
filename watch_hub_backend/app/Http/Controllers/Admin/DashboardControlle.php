<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Watch;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // Get dashboard stats
    public function index(Request $request)
    {
        // Get date range
        $fromDate = $request->from_date ?? now()->startOfMonth();
        $toDate = $request->to_date ?? now();

        // Basic stats
        $totalOrders = Order::count();
        $totalUsers = User::where('role', 'customer')->count();
        $totalProducts = Watch::count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        // Orders by status
        $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Recent orders
        $recentOrders = Order::with(['user', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Monthly revenue
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top selling products
        $topProducts = Watch::withCount(['orderItems' => function ($q) {
            $q->whereHas('order', function ($sub) {
                $sub->where('payment_status', 'paid');
            });
        }])
            ->orderBy('order_items_count', 'desc')
            ->limit(5)
            ->get();

        // Low stock products
        $lowStock = Watch::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->limit(10)
            ->get();

        // Out of stock products
        $outOfStock = Watch::where('stock', 0)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Pending reviews
        $pendingReviews = Review::where('status', 'pending')->count();

        // Recent users
        $recentUsers = User::where('role', 'customer')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_orders' => $totalOrders,
                    'total_users' => $totalUsers,
                    'total_products' => $totalProducts,
                    'total_revenue' => $totalRevenue,
                ],
                'orders_by_status' => $ordersByStatus,
                'recent_orders' => $recentOrders,
                'monthly_revenue' => $monthlyRevenue,
                'top_products' => $topProducts,
                'low_stock' => $lowStock,
                'out_of_stock' => $outOfStock,
                'pending_reviews' => $pendingReviews,
                'recent_users' => $recentUsers,
            ]
        ]);
    }

    // Get revenue stats
    public function revenue(Request $request)
    {
        $period = $request->period ?? 'month'; // day, week, month, year

        $query = Order::where('payment_status', 'paid');

        switch ($period) {
            case 'day':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        $total = $query->sum('total_amount');
        $count = $query->count();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'total_revenue' => $total,
                'order_count' => $count,
                'average_order_value' => $count > 0 ? $total / $count : 0,
            ]
        ]);
    }

    // Get sales trend
    public function salesTrend(Request $request)
    {
        $days = $request->days ?? 30;

        $sales = Order::where('payment_status', 'paid')
            ->whereDate('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }

    // Get top customers
    public function topCustomers(Request $request)
    {
        $limit = $request->limit ?? 10;

        $customers = User::where('role', 'customer')
            ->withCount(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }])
            ->withSum(['orders' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total_amount')
            ->orderBy('orders_sum_total_amount', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $customers
        ]);
    }

    // Get dashboard widgets
    public function widgets()
    {
        // Pending orders count
        $pendingOrders = Order::where('status', 'pending')->count();

        // Today's orders
        $todayOrders = Order::whereDate('created_at', today())->count();

        // New users today
        $newUsersToday = User::whereDate('created_at', today())
            ->where('role', 'customer')
            ->count();

        // Reviews this week
        $reviewsThisWeek = Review::whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ])->count();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_orders' => $pendingOrders,
                'today_orders' => $todayOrders,
                'new_users_today' => $newUsersToday,
                'reviews_this_week' => $reviewsThisWeek,
            ]
        ]);
    }

    // Get quick actions (for admin panel sidebar)
    public function quickActions()
    {
        $actions = [
            [
                'label' => 'Add Product',
                'icon' => 'plus',
                'route' => '/admin/products/create'
            ],
            [
                'label' => 'View Orders',
                'icon' => 'shopping-bag',
                'route' => '/admin/orders'
            ],
            [
                'label' => 'Manage Users',
                'icon' => 'users',
                'route' => '/admin/users'
            ],
            [
                'label' => 'Pending Reviews',
                'icon' => 'star',
                'route' => '/admin/reviews'
            ],
            [
                'label' => 'Create Coupon',
                'icon' => 'ticket',
                'route' => '/admin/coupons/create'
            ],
            [
                'label' => 'Settings',
                'icon' => 'settings',
                'route' => '/admin/settings'
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $actions
        ]);
    }
}
