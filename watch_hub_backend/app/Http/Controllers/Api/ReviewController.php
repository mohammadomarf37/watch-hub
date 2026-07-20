<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use App\Models\Watch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Get all reviews for a watch
    public function index($watchId, Request $request)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        $query = $watch->reviews()->where('status', 'approved');

        // Filter by rating
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        // Sort
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $reviews = $query->with('user')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'watch' => [
                    'id' => $watch->id,
                    'model' => $watch->model,
                    'rating' => $watch->rating,
                    'rating_count' => $watch->rating_count
                ],
                'reviews' => $reviews
            ]
        ]);
    }

    // Create new review
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'watch_id' => 'required|exists:watches,id',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:10|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|max:500',
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Check if user already reviewed this watch
        $existing = Review::where('user_id', $user->id)
            ->where('watch_id', $request->watch_id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this watch'
            ], 409);
        }

        // If order_id provided, verify user purchased this watch
        $isVerified = false;
        if ($request->order_id) {
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->where('status', 'delivered')
                ->first();

            if ($order) {
                $hasItem = $order->items()->where('watch_id', $request->watch_id)->exists();
                if ($hasItem) {
                    $isVerified = true;
                }
            }
        }

        $review = Review::create([
            'user_id' => $user->id,
            'watch_id' => $request->watch_id,
            'order_id' => $request->order_id,
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
            'images' => $request->images,
            'is_verified_purchase' => $isVerified,
            'status' => 'pending', // Admin approval required
        ]);

        // Update watch rating
        $this->updateWatchRating($request->watch_id);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully. Awaiting approval.',
            'data' => $review
        ], 201);
    }

    // Update review
    public function update(Request $request, $id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found or you do not have permission'
            ], 404);
        }

        // Check if review can be updated (only pending reviews)
        if ($review->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Review cannot be updated as it is already ' . $review->status
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'sometimes|string|min:10|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['rating', 'title', 'comment', 'images']));

        // Update watch rating if rating changed
        if ($request->has('rating')) {
            $this->updateWatchRating($review->watch_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review->fresh()
        ]);
    }

    // Delete review
    public function destroy(Request $request, $id)
    {
        $review = Review::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found or you do not have permission'
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

    // Get user's reviews
    public function myReviews(Request $request)
    {
        $reviews = $request->user()->reviews()
            ->with('watch.brand')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    // Get review summary for a watch
    public function summary($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        $reviews = $watch->reviews()->where('status', 'approved')->get();

        $ratingCounts = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        foreach ($reviews as $review) {
            if (isset($ratingCounts[$review->rating])) {
                $ratingCounts[$review->rating]++;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => $watch->rating,
                'total_reviews' => $watch->rating_count,
                'rating_distribution' => $ratingCounts,
                'rating_percentages' => [
                    5 => $watch->rating_count > 0 ? round(($ratingCounts[5] / $watch->rating_count) * 100) : 0,
                    4 => $watch->rating_count > 0 ? round(($ratingCounts[4] / $watch->rating_count) * 100) : 0,
                    3 => $watch->rating_count > 0 ? round(($ratingCounts[3] / $watch->rating_count) * 100) : 0,
                    2 => $watch->rating_count > 0 ? round(($ratingCounts[2] / $watch->rating_count) * 100) : 0,
                    1 => $watch->rating_count > 0 ? round(($ratingCounts[1] / $watch->rating_count) * 100) : 0,
                ]
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
