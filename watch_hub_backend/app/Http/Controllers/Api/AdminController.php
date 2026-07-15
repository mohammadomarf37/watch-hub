<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        return response()->json([
            'stats' => [
                'users' => User::count(),
                'products' => Product::count(),
                'orders' => Order::count(),
                'revenue' => (float) Order::whereNotIn('status', ['cancelled', 'refunded'])->sum('total'),
                'pending_reviews' => Review::where('is_approved', false)->count(),
                'open_tickets' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
            ],
            'recent_orders' => Order::with('user')->latest()->take(8)->get(['id', 'order_number', 'user_id', 'status', 'total', 'created_at']),
        ]);
    }
}
