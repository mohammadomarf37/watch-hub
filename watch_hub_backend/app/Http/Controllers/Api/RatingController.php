<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Watch;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    // Get rating for a single watch
    public function getRating($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'watch_id' => $watch->id,
                'model' => $watch->model,
                'average_rating' => $watch->rating,
                'total_ratings' => $watch->rating_count,
                'rating_distribution' => $this->getRatingDistribution($watchId)
            ]
        ]);
    }

    // Get ratings for multiple watches (bulk)
    public function getBulkRatings(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'watch_ids' => 'required|array',
            'watch_ids.*' => 'exists:watches,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $watches = Watch::whereIn('id', $request->watch_ids)
            ->select('id', 'model', 'rating', 'rating_count')
            ->get();

        $ratings = [];

        foreach ($watches as $watch) {
            $ratings[] = [
                'watch_id' => $watch->id,
                'model' => $watch->model,
                'average_rating' => $watch->rating,
                'total_ratings' => $watch->rating_count
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $ratings
        ]);
    }

    // Get top rated watches
    public function topRated(Request $request)
    {
        $limit = $request->limit ?? 10;
        $minRatings = $request->min_ratings ?? 3;

        $watches = Watch::where('is_active', true)
            ->where('rating_count', '>=', $minRatings)
            ->whereNotNull('rating')
            ->with(['brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->orderBy('rating', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get recently rated watches
    public function recentlyRated(Request $request)
    {
        $limit = $request->limit ?? 10;

        $watches = Watch::where('is_active', true)
            ->whereNotNull('rating')
            ->with(['brand', 'images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $watches
        ]);
    }

    // Get rating breakdown for a watch
    public function getRatingBreakdown($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 422);
        }

        $breakdown = $this->getRatingDistribution($watchId);

        return response()->json([
            'success' => true,
            'data' => [
                'watch_id' => $watch->id,
                'model' => $watch->model,
                'total_ratings' => $watch->rating_count,
                'breakdown' => $breakdown
            ]
        ]);
    }

    // Get rating statistics (for dashboard)
    public function getStatistics(Request $request)
    {
        $totalWatches = Watch::where('is_active', true)->count();
        $ratedWatches = Watch::where('is_active', true)->whereNotNull('rating')->count();
        $unratedWatches = $totalWatches - $ratedWatches;

        $averageRating = Watch::where('is_active', true)
            ->whereNotNull('rating')
            ->avg('rating');

        $ratingCounts = [
            1 => Watch::where('is_active', true)->where('rating', '>=', 1)->where('rating', '<', 2)->count(),
            2 => Watch::where('is_active', true)->where('rating', '>=', 2)->where('rating', '<', 3)->count(),
            3 => Watch::where('is_active', true)->where('rating', '>=', 3)->where('rating', '<', 4)->count(),
            4 => Watch::where('is_active', true)->where('rating', '>=', 4)->where('rating', '<', 5)->count(),
            5 => Watch::where('is_active', true)->where('rating', '>=', 5)->count(),
        ];

        // Get top rated brands
        $topBrands = \DB::table('watches')
            ->join('brands', 'watches.brand_id', '=', 'brands.id')
            ->select('brands.id', 'brands.name', \DB::raw('AVG(watches.rating) as avg_rating'))
            ->where('watches.is_active', true)
            ->whereNotNull('watches.rating')
            ->groupBy('brands.id', 'brands.name')
            ->having('avg_rating', '>=', 4)
            ->orderBy('avg_rating', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_watches' => $totalWatches,
                'rated_watches' => $ratedWatches,
                'unrated_watches' => $unratedWatches,
                'average_rating' => round($averageRating, 2) ?? 0,
                'rating_distribution' => $ratingCounts,
                'top_brands' => $topBrands
            ]
        ]);
    }

    // Get rating trend for a watch
    public function getRatingTrend($watchId)
    {
        $watch = Watch::find($watchId);

        if (!$watch) {
            return response()->json([
                'success' => false,
                'message' => 'Watch not found'
            ], 404);
        }

        // Get reviews grouped by month for last 6 months
        $trend = $watch->reviews()
            ->where('status', 'approved')
            ->select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                \DB::raw('AVG(rating) as avg_rating'),
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'watch_id' => $watch->id,
                'model' => $watch->model,
                'trend' => $trend
            ]
        ]);
    }

    // Helper: Get rating distribution
    private function getRatingDistribution($watchId)
    {
        $ratings = \DB::table('reviews')
            ->where('watch_id', $watchId)
            ->where('status', 'approved')
            ->select('rating', \DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->get();

        $distribution = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        foreach ($ratings as $rating) {
            $distribution[$rating->rating] = $rating->count;
        }

        return $distribution;
    }
}
