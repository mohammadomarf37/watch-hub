<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends AdminController
{
    // Get all reviews with filters
    public function index(Request $request)
    {
        $query = Review::with(['user', 'watch.brand']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        // Filter by watch
        if ($request->has('watch_id')) {
            $query->where('watch_id', $request->watch_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by user or comment
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('comment', 'like', '%' . $request->search . '%')
                    ->orWhere('title', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($sub) use ($request) {
                        $sub->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get counts for statuses
        $counts = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'reviews' => $reviews,
                'counts' => $counts
            ]
        ]);
    }

    // Get single review
    public function show($id)
    {
        $review = Review::with(['user', 'watch.brand', 'order'])->find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $review
        ]);
    }

    // Approve review
    public function approve($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->status = 'approved';
        $review->save();

        // Update watch rating
        $this->updateWatchRating($review->watch_id);

        return response()->json([
            'success' => true,
            'message' => 'Review approved successfully',
            'data' => $review
        ]);
    }

    // Reject review
    public function reject($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->status = 'rejected';
        $review->save();

        return response()->json([
            'success' => true,
            'message' => 'Review rejected successfully',
            'data' => $review
        ]);
    }

    // Delete review
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $watchId = $review->watch_id;
        $review->delete();

        // Update watch rating
        $this->updateWatchRating($watchId);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully'
        ]);
    }

    // Bulk approve reviews
    public function bulkApprove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $reviews = Review::whereIn('id', $request->ids)->get();
        $watchIds = $reviews->pluck('watch_id')->unique();

        Review::whereIn('id', $request->ids)->update(['status' => 'approved']);

        // Update ratings for affected watches
        foreach ($watchIds as $watchId) {
            $this->updateWatchRating($watchId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reviews approved successfully'
        ]);
    }

    // Bulk reject reviews
    public function bulkReject(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        Review::whereIn('id', $request->ids)->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Reviews rejected successfully'
        ]);
    }

    // Bulk delete reviews
    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:reviews,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $reviews = Review::whereIn('id', $request->ids)->get();
        $watchIds = $reviews->pluck('watch_id')->unique();

        Review::whereIn('id', $request->ids)->delete();

        // Update ratings for affected watches
        foreach ($watchIds as $watchId) {
            $this->updateWatchRating($watchId);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reviews deleted successfully'
        ]);
    }

    // Get review statistics
    public function stats()
    {
        $stats = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'rejected' => Review::where('status', 'rejected')->count(),
            'average_rating' => Review::where('status', 'approved')->avg('rating') ?? 0,
            'ratings_distribution' => [
                1 => Review::where('status', 'approved')->where('rating', 1)->count(),
                2 => Review::where('status', 'approved')->where('rating', 2)->count(),
                3 => Review::where('status', 'approved')->where('rating', 3)->count(),
                4 => Review::where('status', 'approved')->where('rating', 4)->count(),
                5 => Review::where('status', 'approved')->where('rating', 5)->count(),
            ],
            'today' => Review::whereDate('created_at', today())->count(),
            'this_week' => Review::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => Review::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    // Get reviews by watch
    public function getByWatch($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        $reviews = Review::where('watch_id', $watchId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'watch' => $watch->model,
                'total_reviews' => $reviews->count(),
                'average_rating' => $watch->rating,
                'reviews' => $reviews
            ]
        ]);
    }

    // Helper: Update watch rating
    private function updateWatchRating($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return;
        }

        $reviews = $watch->reviews()->where('status', 'approved')->get();
        $count = $reviews->count();

        if ($count > 0) {
            $average = $reviews->avg('rating');
            $watch->rating = round($average, 2);
            $watch->rating_count = $count;
        } else {
            $watch->rating = null;
            $watch->rating_count = 0;
        }

        $watch->save();
    }
}
