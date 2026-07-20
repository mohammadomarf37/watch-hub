<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\WatchController;
use App\Http\Controllers\Api\WatchVariantController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =============================================
// PUBLIC ROUTES (No Auth Required)
// =============================================

// Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

// Admin Login (Public)
Route::post('/admin/login', [AdminController::class, 'login']);

// Brand Routes
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/brands/{id}', [BrandController::class, 'show']);
Route::get('/brands/slug/{slug}', [BrandController::class, 'getBySlug']);
Route::get('/brands/{id}/watches', [BrandController::class, 'getWatches']);
Route::get('/brands/top', [BrandController::class, 'topBrands']);
Route::get('/brands/with-count', [BrandController::class, 'getWithCount']);

// Category Routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/slug/{slug}', [CategoryController::class, 'getBySlug']);
Route::get('/categories/{id}/watches', [CategoryController::class, 'getWatches']);
Route::get('/categories/with-count', [CategoryController::class, 'getWithCount']);
Route::get('/categories/main', [CategoryController::class, 'getMainCategories']);
Route::get('/categories/{id}/subcategories', [CategoryController::class, 'getSubcategories']);

// Watch Routes
Route::get('/watches', [WatchController::class, 'index']);
Route::get('/watches/featured', [WatchController::class, 'featured']);
Route::get('/watches/new-arrivals', [WatchController::class, 'newArrivals']);
Route::get('/watches/recommended', [WatchController::class, 'recommended']);
Route::get('/watches/{id}', [WatchController::class, 'show']);
Route::get('/watches/slug/{slug}', [WatchController::class, 'getBySlug']);
Route::get('/watches/brand/{brandId}', [WatchController::class, 'getByBrand']);
Route::get('/watches/category/{categoryId}', [WatchController::class, 'getByCategory']);
Route::get('/watches/{id}/reviews', [WatchController::class, 'getReviews']);
Route::get('/watches/{id}/stock', [WatchController::class, 'checkStock']);

// Variant Routes
Route::get('/variants', [WatchVariantController::class, 'index']);
Route::get('/variants/{id}', [WatchVariantController::class, 'show']);
Route::get('/variants/watch/{watchId}', [WatchVariantController::class, 'getByWatch']);
Route::get('/variants/watch/{watchId}/colors', [WatchVariantController::class, 'getColors']);
Route::get('/variants/watch/{watchId}/sizes', [WatchVariantController::class, 'getSizes']);
Route::get('/variants/sku/{sku}', [WatchVariantController::class, 'getBySku']);
Route::get('/variants/{id}/stock', [WatchVariantController::class, 'checkStock']);
Route::get('/variants/{id}/price', [WatchVariantController::class, 'getPrice']);
Route::get('/variants/low-stock', [WatchVariantController::class, 'lowStock']);

// Search Routes
Route::get('/search', [SearchController::class, 'search']);
Route::get('/search/watches', [SearchController::class, 'searchWatches']);
Route::get('/search/advanced', [SearchController::class, 'advancedSearch']);
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete']);
Route::get('/search/filters', [SearchController::class, 'filterOptions']);
Route::get('/search/trending', [SearchController::class, 'trendingSearches']);

// Rating Routes (Public)
Route::get('/ratings/watch/{watchId}', [RatingController::class, 'getRating']);
Route::post('/ratings/bulk', [RatingController::class, 'getBulkRatings']);
Route::get('/ratings/top-rated', [RatingController::class, 'topRated']);
Route::get('/ratings/recently-rated', [RatingController::class, 'recentlyRated']);
Route::get('/ratings/watch/{watchId}/breakdown', [RatingController::class, 'getRatingBreakdown']);
Route::get('/ratings/statistics', [RatingController::class, 'getStatistics']);
Route::get('/ratings/watch/{watchId}/trend', [RatingController::class, 'getRatingTrend']);

// Review Routes (Public)
Route::get('/reviews/watch/{watchId}', [ReviewController::class, 'index']);
Route::get('/reviews/watch/{watchId}/summary', [ReviewController::class, 'summary']);

// Shipping Routes (Public)
Route::get('/shipping/methods', [ShippingController::class, 'getMethods']);
Route::post('/shipping/calculate', [ShippingController::class, 'calculateRate']);
Route::post('/shipping/all-rates', [ShippingController::class, 'getAllRates']);
Route::get('/shipping/countries', [ShippingController::class, 'getCountries']);

// Setting Routes (Public)
Route::get('/settings/public', [SettingController::class, 'getPublicSettings']);
Route::get('/settings/group/{group}', [SettingController::class, 'getByGroup']);
Route::get('/settings/{key}', [SettingController::class, 'getByKey']);
Route::get('/settings/app', [SettingController::class, 'appSettings']);
Route::get('/settings/currency', [SettingController::class, 'currencySettings']);
Route::get('/settings/shipping', [SettingController::class, 'shippingSettings']);
Route::get('/settings/tax', [SettingController::class, 'taxSettings']);
Route::get('/settings/seo', [SettingController::class, 'seoSettings']);

// Payment Callback (Webhook - No Auth)
Route::post('/payments/callback', [PaymentController::class, 'callback']);

// =============================================
// AUTHENTICATED ROUTES (Auth Required)
// =============================================

Route::middleware('auth:sanctum')->group(function () {

    // Auth Routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/image', [ProfileController::class, 'updateProfileImage']);
    Route::post('/profile/change-password', [ProfileController::class, 'changePassword']);
    Route::delete('/profile', [ProfileController::class, 'deleteAccount']);
    Route::get('/profile/stats', [ProfileController::class, 'stats']);
    Route::get('/profile/activity', [ProfileController::class, 'activity']);
    Route::get('/profile/notifications', [ProfileController::class, 'notificationPreferences']);
    Route::put('/profile/notifications', [ProfileController::class, 'updateNotificationPreferences']);

    // Address Routes
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::put('/addresses/{id}/default', [AddressController::class, 'setDefault']);
    Route::get('/addresses/default', [AddressController::class, 'getDefault']);
    Route::get('/addresses/shipping', [AddressController::class, 'getShippingAddresses']);
    Route::get('/addresses/billing', [AddressController::class, 'getBillingAddresses']);

    // Payment Method Routes
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::get('/payment-methods/{id}', [PaymentMethodController::class, 'show']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);
    Route::put('/payment-methods/{id}/default', [PaymentMethodController::class, 'setDefault']);
    Route::get('/payment-methods/default', [PaymentMethodController::class, 'getDefault']);
    Route::get('/payment-methods/providers', [PaymentMethodController::class, 'getProviders']);

    // Cart Routes
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::get('/cart/count', [CartController::class, 'count']);
    Route::get('/cart/subtotal', [CartController::class, 'subtotal']);
    Route::post('/cart/{id}/move-to-wishlist', [CartController::class, 'moveToWishlist']);

    // Checkout Routes
    Route::get('/checkout/summary', [CheckoutController::class, 'summary']);
    Route::post('/checkout/process', [CheckoutController::class, 'process']);
    Route::post('/checkout/confirm', [CheckoutController::class, 'confirm']);

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders/order-number/{orderNumber}', [OrderController::class, 'getByOrderNumber']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);
    Route::get('/orders/{id}/history', [OrderController::class, 'getStatusHistory']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);
    Route::get('/orders/summary', [OrderController::class, 'summary']);

    // Wishlist Routes
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/add', [WishlistController::class, 'add']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'remove']);
    Route::delete('/wishlist/remove', [WishlistController::class, 'removeByVariant']);
    Route::post('/wishlist/{id}/move-to-cart', [WishlistController::class, 'moveToCart']);
    Route::post('/wishlist/move-all-to-cart', [WishlistController::class, 'moveAllToCart']);
    Route::delete('/wishlist/clear', [WishlistController::class, 'clear']);
    Route::get('/wishlist/check', [WishlistController::class, 'check']);
    Route::get('/wishlist/count', [WishlistController::class, 'count']);

    // Review Routes
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    Route::get('/reviews/my-reviews', [ReviewController::class, 'myReviews']);

    // Payment Routes
    Route::post('/payments/process', [PaymentController::class, 'process']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);
    Route::get('/payments/history', [PaymentController::class, 'history']);
    Route::get('/payments/order/{orderId}/status', [PaymentController::class, 'getStatus']);
    Route::post('/payments/refund', [PaymentController::class, 'refund']);
    Route::get('/payments/providers', [PaymentController::class, 'getProviders']);

    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll']);
    Route::get('/notifications/preferences', [NotificationController::class, 'preferences']);
    Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::get('/notifications/type/{type}', [NotificationController::class, 'getByType']);

    // Coupon Routes
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);
    Route::get('/coupons/available', [CouponController::class, 'getAvailableCoupons']);
    Route::get('/coupons/usage-history', [CouponController::class, 'getUsageHistory']);
    Route::get('/coupons/code/{code}', [CouponController::class, 'getByCode']);

    // Shipping Routes (Auth)
    Route::post('/shipping/address-rate', [ShippingController::class, 'getRateByAddress']);

    // Order Status Routes (Admin Only)
    Route::put('/orders/{id}/status', [OrderStatusController::class, 'updateStatus']);
    Route::put('/orders/bulk-status', [OrderStatusController::class, 'bulkUpdateStatus']);
    Route::get('/orders/{id}/available-statuses', [OrderStatusController::class, 'getAvailableStatuses']);
    Route::get('/orders/status-counts', [OrderStatusController::class, 'getStatusCounts']);
    Route::get('/orders/{id}/timeline', [OrderStatusController::class, 'getTimeline']);
});

// =============================================
// ADMIN ROUTES (Auth + Admin Role Required)
// =============================================

Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {

    // Admin Auth
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/profile', [AdminController::class, 'profile']);
    Route::put('/profile', [AdminController::class, 'updateProfile']);
    Route::post('/change-password', [AdminController::class, 'changePassword']);
    Route::get('/stats', [AdminController::class, 'stats']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/revenue', [DashboardController::class, 'revenue']);
    Route::get('/dashboard/sales-trend', [DashboardController::class, 'salesTrend']);
    Route::get('/dashboard/top-customers', [DashboardController::class, 'topCustomers']);
    Route::get('/dashboard/widgets', [DashboardController::class, 'widgets']);
    Route::get('/dashboard/quick-actions', [DashboardController::class, 'quickActions']);

    // Product Management
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    Route::put('/products/{id}/toggle', [AdminProductController::class, 'toggleStatus']);
    Route::put('/products/{id}/featured', [AdminProductController::class, 'toggleFeatured']);
    Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDelete']);
    Route::post('/products/bulk-stock', [AdminProductController::class, 'bulkUpdateStock']);

    // Order Management
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    Route::delete('/orders/{id}', [AdminOrderController::class, 'destroy']);
    Route::get('/orders/stats', [AdminOrderController::class, 'stats']);
    Route::get('/orders/date-range', [AdminOrderController::class, 'getByDateRange']);
    Route::get('/orders/export', [AdminOrderController::class, 'export']);

    // User Management
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}/toggle', [UserController::class, 'toggleStatus']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::get('/users/stats', [UserController::class, 'stats']);
    Route::get('/users/{id}/activity', [UserController::class, 'activity']);
    Route::post('/users/bulk-delete', [UserController::class, 'bulkDelete']);
    Route::post('/users/bulk-status', [UserController::class, 'bulkUpdateStatus']);

    // Review Management
    Route::get('/reviews', [AdminReviewController::class, 'index']);
    Route::get('/reviews/{id}', [AdminReviewController::class, 'show']);
    Route::put('/reviews/{id}/approve', [AdminReviewController::class, 'approve']);
    Route::put('/reviews/{id}/reject', [AdminReviewController::class, 'reject']);
    Route::delete('/reviews/{id}', [AdminReviewController::class, 'destroy']);
    Route::post('/reviews/bulk-approve', [AdminReviewController::class, 'bulkApprove']);
    Route::post('/reviews/bulk-reject', [AdminReviewController::class, 'bulkReject']);
    Route::post('/reviews/bulk-delete', [AdminReviewController::class, 'bulkDelete']);
    Route::get('/reviews/stats', [AdminReviewController::class, 'stats']);
    Route::get('/reviews/watch/{watchId}', [AdminReviewController::class, 'getByWatch']);

    // Coupon Management
    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::get('/coupons/{id}', [AdminCouponController::class, 'show']);
    Route::put('/coupons/{id}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy']);
    Route::put('/coupons/{id}/toggle', [AdminCouponController::class, 'toggleStatus']);
    Route::get('/coupons/{id}/usage', [AdminCouponController::class, 'usageReport']);
    Route::get('/coupons/stats', [AdminCouponController::class, 'stats']);
    Route::post('/coupons/bulk-delete', [AdminCouponController::class, 'bulkDelete']);
    Route::post('/coupons/bulk-status', [AdminCouponController::class, 'bulkToggleStatus']);
    Route::get('/coupons/products', [AdminCouponController::class, 'getAvailableProducts']);

    // Brand Management
    Route::get('/brands', [AdminBrandController::class, 'index']);
    Route::post('/brands', [AdminBrandController::class, 'store']);
    Route::get('/brands/{id}', [AdminBrandController::class, 'show']);
    Route::put('/brands/{id}', [AdminBrandController::class, 'update']);
    Route::delete('/brands/{id}', [AdminBrandController::class, 'destroy']);
    Route::put('/brands/{id}/toggle', [AdminBrandController::class, 'toggleStatus']);
    Route::post('/brands/bulk-delete', [AdminBrandController::class, 'bulkDelete']);
    Route::post('/brands/bulk-status', [AdminBrandController::class, 'bulkUpdateStatus']);
    Route::get('/brands/stats', [AdminBrandController::class, 'stats']);
    Route::get('/brands/list', [AdminBrandController::class, 'getList']);

    // Category Management
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{id}', [AdminCategoryController::class, 'show']);
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy']);
    Route::put('/categories/{id}/toggle', [AdminCategoryController::class, 'toggleStatus']);
    Route::get('/categories/hierarchy', [AdminCategoryController::class, 'getHierarchy']);
    Route::get('/categories/with-parents', [AdminCategoryController::class, 'getWithParents']);
    Route::post('/categories/bulk-delete', [AdminCategoryController::class, 'bulkDelete']);
    Route::post('/categories/bulk-status', [AdminCategoryController::class, 'bulkUpdateStatus']);
    Route::get('/categories/stats', [AdminCategoryController::class, 'stats']);
    Route::get('/categories/list', [AdminCategoryController::class, 'getList']);

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::put('/settings', [SettingsController::class, 'update']);
    Route::put('/settings/{key}', [SettingsController::class, 'updateSingle']);
    Route::get('/settings/group/{group}', [SettingsController::class, 'getByGroup']);
    Route::post('/settings', [SettingsController::class, 'store']);
    Route::delete('/settings/{key}', [SettingsController::class, 'destroy']);
    Route::get('/settings/groups', [SettingsController::class, 'getGroups']);
    Route::post('/settings/reset', [SettingsController::class, 'reset']);
    Route::get('/settings/key/{key}', [SettingsController::class, 'getByKey']);
});
