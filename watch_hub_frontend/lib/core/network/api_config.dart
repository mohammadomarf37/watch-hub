/// Central place for backend connection settings.
class ApiConfig {
  ApiConfig._();

  static const String baseUrl = 'http://localhost:8000/api';
  static const Duration timeout = Duration(seconds: 20);

  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';

  // =============================================
  // AUTH
  // =============================================
  static const String login = '$baseUrl/auth/login';
  static const String register = '$baseUrl/auth/register';
  static const String logout = '$baseUrl/auth/logout';
  static const String forgotPassword = '$baseUrl/auth/forgot-password';
  static const String resetPassword = '$baseUrl/auth/reset-password';
  static const String userProfile = '$baseUrl/auth/user';
  static const String changePassword = '$baseUrl/auth/change-password';

  // =============================================
  // PRODUCTS
  // =============================================
  static const String watches = '$baseUrl/watches';
  static const String featuredWatches = '$baseUrl/watches/featured';
  static const String newArrivals = '$baseUrl/watches/new-arrivals';
  static const String recommendedWatches = '$baseUrl/watches/recommended';

  // ✅ FIXED: Watch detail with ID
  static String watchDetail(String id) => '$baseUrl/watches/$id';

  // ✅ FIXED: Watch reviews with ID
  static String watchReviews(String id) => '$baseUrl/watches/$id/reviews';

  static String watchBySlug(String slug) => '$baseUrl/watches/slug/$slug';
  static String watchesByBrand(String brandId) =>
      '$baseUrl/watches/brand/$brandId';
  static String watchesByCategory(String categoryId) =>
      '$baseUrl/watches/category/$categoryId';
  static String checkStock(String id) => '$baseUrl/watches/$id/stock';

  // =============================================
  // BRANDS
  // =============================================
  static const String brands = '$baseUrl/brands';
  static String brandDetail(String id) => '$baseUrl/brands/$id';
  static String brandBySlug(String slug) => '$baseUrl/brands/slug/$slug';
  static String brandWatches(String id) => '$baseUrl/brands/$id/watches';
  static const String topBrands = '$baseUrl/brands/top';
  static const String brandsWithCount = '$baseUrl/brands/with-count';

  // =============================================
  // CATEGORIES
  // =============================================
  static const String categories = '$baseUrl/categories';
  static String categoryDetail(String id) => '$baseUrl/categories/$id';
  static String categoryBySlug(String slug) => '$baseUrl/categories/slug/$slug';
  static String categoryWatches(String id) => '$baseUrl/categories/$id/watches';
  static const String mainCategories = '$baseUrl/categories/main';
  static String categorySubcategories(String id) =>
      '$baseUrl/categories/$id/subcategories';
  static const String categoriesWithCount = '$baseUrl/categories/with-count';

  // =============================================
  // CART
  // =============================================
  static const String cart = '$baseUrl/cart';
  static const String cartAdd = '$baseUrl/cart/add';
  static String cartUpdate(String id) => '$baseUrl/cart/$id';
  static String cartRemove(String id) => '$baseUrl/cart/$id';
  static const String cartClear = '$baseUrl/cart/clear';
  static const String cartCount = '$baseUrl/cart/count';
  static const String cartSubtotal = '$baseUrl/cart/subtotal';
  static String cartMoveToWishlist(String id) =>
      '$baseUrl/cart/$id/move-to-wishlist';

  // =============================================
  // WISHLIST
  // =============================================
  static const String wishlist = '$baseUrl/wishlist';
  static const String wishlistAdd = '$baseUrl/wishlist/add';
  static String wishlistRemove(String id) => '$baseUrl/wishlist/$id';
  static const String wishlistRemoveByVariant = '$baseUrl/wishlist/remove';
  static String wishlistMoveToCart(String id) =>
      '$baseUrl/wishlist/$id/move-to-cart';
  static const String wishlistMoveAllToCart =
      '$baseUrl/wishlist/move-all-to-cart';
  static const String wishlistClear = '$baseUrl/wishlist/clear';
  static const String wishlistCheck = '$baseUrl/wishlist/check';
  static const String wishlistCount = '$baseUrl/wishlist/count';

  // =============================================
  // ORDERS
  // =============================================
  static const String orders = '$baseUrl/orders';
  static String orderDetail(String id) => '$baseUrl/orders/$id';
  static String orderByNumber(String number) =>
      '$baseUrl/orders/order-number/$number';
  static String orderCancel(String id) => '$baseUrl/orders/$id/cancel';
  static String orderTrack(String id) => '$baseUrl/orders/$id/track';
  static String orderHistory(String id) => '$baseUrl/orders/$id/history';
  static String orderInvoice(String id) => '$baseUrl/orders/$id/invoice';
  static const String orderSummary = '$baseUrl/orders/summary';

  // =============================================
  // CHECKOUT
  // =============================================
  static const String checkoutSummary = '$baseUrl/checkout/summary';
  static const String checkoutProcess = '$baseUrl/checkout/process';
  static const String checkoutConfirm = '$baseUrl/checkout/confirm';

  // =============================================
  // PROFILE
  // =============================================
  static const String profile = '$baseUrl/profile';
  static const String profileImage = '$baseUrl/profile/image';
  static const String profileStats = '$baseUrl/profile/stats';
  static const String profileActivity = '$baseUrl/profile/activity';
  static const String profileNotifications = '$baseUrl/profile/notifications';

  // =============================================
  // ADDRESSES
  // =============================================
  static const String addresses = '$baseUrl/addresses';
  static String addressDetail(String id) => '$baseUrl/addresses/$id';
  static String addressDefault(String id) => '$baseUrl/addresses/$id/default';
  static const String addressDefaultGet = '$baseUrl/addresses/default';
  static const String addressShipping = '$baseUrl/addresses/shipping';
  static const String addressBilling = '$baseUrl/addresses/billing';

  // =============================================
  // REVIEWS ✅ FIXED
  // =============================================
  static const String reviews = '$baseUrl/reviews';
  static String reviewsByWatch(String watchId) =>
      '$baseUrl/reviews/watch/$watchId';
  static String reviewSummary(String watchId) =>
      '$baseUrl/reviews/watch/$watchId/summary';
  static const String myReviews = '$baseUrl/reviews/my-reviews';

  // =============================================
  // PAYMENTS
  // =============================================
  static const String paymentsProcess = '$baseUrl/payments/process';
  static const String paymentsVerify = '$baseUrl/payments/verify';
  static const String paymentsHistory = '$baseUrl/payments/history';
  static String paymentStatus(String orderId) =>
      '$baseUrl/payments/order/$orderId/status';
  static const String paymentsRefund = '$baseUrl/payments/refund';
  static const String paymentsProviders = '$baseUrl/payments/providers';

  // =============================================
  // NOTIFICATIONS
  // =============================================
  static const String notifications = '$baseUrl/notifications';
  static String notificationRead(String id) =>
      '$baseUrl/notifications/$id/read';
  static const String notificationReadAll = '$baseUrl/notifications/read-all';
  static const String notificationUnreadCount =
      '$baseUrl/notifications/unread-count';

  // =============================================
  // COUPONS
  // =============================================
  static const String couponValidate = '$baseUrl/coupons/validate';
  static const String couponAvailable = '$baseUrl/coupons/available';
  static const String couponUsageHistory = '$baseUrl/coupons/usage-history';
  static String couponByCode(String code) => '$baseUrl/coupons/code/$code';

  // =============================================
  // SHIPPING
  // =============================================
  static const String shippingMethods = '$baseUrl/shipping/methods';
  static const String shippingCalculate = '$baseUrl/shipping/calculate';
  static const String shippingAllRates = '$baseUrl/shipping/all-rates';
  static const String shippingAddressRate = '$baseUrl/shipping/address-rate';
  static const String shippingCountries = '$baseUrl/shipping/countries';

  // =============================================
  // SEARCH
  // =============================================
  static const String search = '$baseUrl/search';
  static const String searchWatches = '$baseUrl/search/watches';
  static const String searchAdvanced = '$baseUrl/search/advanced';
  static const String searchAutocomplete = '$baseUrl/search/autocomplete';
  static const String searchFilters = '$baseUrl/search/filters';
  static const String searchTrending = '$baseUrl/search/trending';

  // =============================================
  // SETTINGS
  // =============================================
  static const String settingsPublic = '$baseUrl/settings/public';
  static const String settingsApp = '$baseUrl/settings/app';
  static const String settingsCurrency = '$baseUrl/settings/currency';
  static const String settingsShipping = '$baseUrl/settings/shipping';
  static const String settingsTax = '$baseUrl/settings/tax';
  static const String settingsSeo = '$baseUrl/settings/seo';
  static String settingsByKey(String key) => '$baseUrl/settings/$key';
  static String settingsByGroup(String group) =>
      '$baseUrl/settings/group/$group';

  // =============================================
  // VARIANTS
  // =============================================
  static const String variants = '$baseUrl/variants';
  static String variantsByWatch(String watchId) =>
      '$baseUrl/variants/watch/$watchId';
  static String variantsColors(String watchId) =>
      '$baseUrl/variants/watch/$watchId/colors';
  static String variantsSizes(String watchId) =>
      '$baseUrl/variants/watch/$watchId/sizes';
  static String variantsBySku(String sku) => '$baseUrl/variants/sku/$sku';
  static const String variantsLowStock = '$baseUrl/variants/low-stock';

  // =============================================
  // RATINGS
  // =============================================
  static String ratingsWatch(String watchId) =>
      '$baseUrl/ratings/watch/$watchId';
  static const String ratingsBulk = '$baseUrl/ratings/bulk';
  static const String ratingsTopRated = '$baseUrl/ratings/top-rated';
  static const String ratingsRecentlyRated = '$baseUrl/ratings/recently-rated';
  static String ratingsBreakdown(String watchId) =>
      '$baseUrl/ratings/watch/$watchId/breakdown';
  static const String ratingsStatistics = '$baseUrl/ratings/statistics';
  static String ratingsTrend(String watchId) =>
      '$baseUrl/ratings/watch/$watchId/trend';

  // =============================================
  // PAYMENT METHODS
  // =============================================
  static const String paymentMethods = '$baseUrl/payment-methods';
  static String paymentMethodDetail(String id) =>
      '$baseUrl/payment-methods/$id';
  static String paymentMethodDefault(String id) =>
      '$baseUrl/payment-methods/$id/default';
  static const String paymentMethodsDefaultGet =
      '$baseUrl/payment-methods/default';
  static const String paymentMethodsProviders =
      '$baseUrl/payment-methods/providers';

  // =============================================
  // ADMIN
  // =============================================
  static const String adminLogin = '$baseUrl/admin/login';
  static const String adminLogout = '$baseUrl/admin/logout';
  static const String adminProfile = '$baseUrl/admin/profile';
  static const String adminDashboard = '$baseUrl/admin/dashboard';
  static const String adminDashboardRevenue =
      '$baseUrl/admin/dashboard/revenue';
  static const String adminDashboardSalesTrend =
      '$baseUrl/admin/dashboard/sales-trend';
  static const String adminDashboardTopCustomers =
      '$baseUrl/admin/dashboard/top-customers';
  static const String adminDashboardWidgets =
      '$baseUrl/admin/dashboard/widgets';
  static const String adminDashboardQuickActions =
      '$baseUrl/admin/dashboard/quick-actions';

  static const String adminProducts = '$baseUrl/admin/products';
  static String adminProductDetail(String id) => '$baseUrl/admin/products/$id';
  static String adminProductToggle(String id) =>
      '$baseUrl/admin/products/$id/toggle';
  static String adminProductFeatured(String id) =>
      '$baseUrl/admin/products/$id/featured';
  static const String adminProductsBulkDelete =
      '$baseUrl/admin/products/bulk-delete';
  static const String adminProductsBulkStock =
      '$baseUrl/admin/products/bulk-stock';

  static const String adminOrders = '$baseUrl/admin/orders';
  static String adminOrderDetail(String id) => '$baseUrl/admin/orders/$id';
  static String adminOrderStatus(String id) =>
      '$baseUrl/admin/orders/$id/status';
  static const String adminOrdersStats = '$baseUrl/admin/orders/stats';
  static const String adminOrdersDateRange = '$baseUrl/admin/orders/date-range';
  static const String adminOrdersExport = '$baseUrl/admin/orders/export';

  static const String adminUsers = '$baseUrl/admin/users';
  static String adminUserDetail(String id) => '$baseUrl/admin/users/$id';
  static String adminUserToggle(String id) => '$baseUrl/admin/users/$id/toggle';
  static const String adminUsersStats = '$baseUrl/admin/users/stats';
  static String adminUserActivity(String id) =>
      '$baseUrl/admin/users/$id/activity';
  static const String adminUsersBulkDelete = '$baseUrl/admin/users/bulk-delete';
  static const String adminUsersBulkStatus = '$baseUrl/admin/users/bulk-status';

  static const String adminReviews = '$baseUrl/admin/reviews';
  static String adminReviewDetail(String id) => '$baseUrl/admin/reviews/$id';
  static String adminReviewApprove(String id) =>
      '$baseUrl/admin/reviews/$id/approve';
  static String adminReviewReject(String id) =>
      '$baseUrl/admin/reviews/$id/reject';
  static const String adminReviewsBulkApprove =
      '$baseUrl/admin/reviews/bulk-approve';
  static const String adminReviewsBulkReject =
      '$baseUrl/admin/reviews/bulk-reject';
  static const String adminReviewsBulkDelete =
      '$baseUrl/admin/reviews/bulk-delete';
  static const String adminReviewsStats = '$baseUrl/admin/reviews/stats';
  static String adminReviewsByWatch(String watchId) =>
      '$baseUrl/admin/reviews/watch/$watchId';

  static const String adminCoupons = '$baseUrl/admin/coupons';
  static String adminCouponDetail(String id) => '$baseUrl/admin/coupons/$id';
  static String adminCouponToggle(String id) =>
      '$baseUrl/admin/coupons/$id/toggle';
  static String adminCouponUsage(String id) =>
      '$baseUrl/admin/coupons/$id/usage';
  static const String adminCouponsStats = '$baseUrl/admin/coupons/stats';
  static const String adminCouponsBulkDelete =
      '$baseUrl/admin/coupons/bulk-delete';
  static const String adminCouponsBulkStatus =
      '$baseUrl/admin/coupons/bulk-status';
  static const String adminCouponsProducts = '$baseUrl/admin/coupons/products';

  static const String adminBrands = '$baseUrl/admin/brands';
  static String adminBrandDetail(String id) => '$baseUrl/admin/brands/$id';
  static String adminBrandToggle(String id) =>
      '$baseUrl/admin/brands/$id/toggle';
  static const String adminBrandsBulkDelete =
      '$baseUrl/admin/brands/bulk-delete';
  static const String adminBrandsBulkStatus =
      '$baseUrl/admin/brands/bulk-status';
  static const String adminBrandsStats = '$baseUrl/admin/brands/stats';
  static const String adminBrandsList = '$baseUrl/admin/brands/list';

  static const String adminCategories = '$baseUrl/admin/categories';
  static String adminCategoryDetail(String id) =>
      '$baseUrl/admin/categories/$id';
  static String adminCategoryToggle(String id) =>
      '$baseUrl/admin/categories/$id/toggle';
  static const String adminCategoriesHierarchy =
      '$baseUrl/admin/categories/hierarchy';
  static const String adminCategoriesWithParents =
      '$baseUrl/admin/categories/with-parents';
  static const String adminCategoriesBulkDelete =
      '$baseUrl/admin/categories/bulk-delete';
  static const String adminCategoriesBulkStatus =
      '$baseUrl/admin/categories/bulk-status';
  static const String adminCategoriesStats = '$baseUrl/admin/categories/stats';
  static const String adminCategoriesList = '$baseUrl/admin/categories/list';

  static const String adminSettings = '$baseUrl/admin/settings';
  static String adminSettingsByKey(String key) =>
      '$baseUrl/admin/settings/$key';
  static String adminSettingsByGroup(String group) =>
      '$baseUrl/admin/settings/group/$group';
  static const String adminSettingsGroups = '$baseUrl/admin/settings/groups';
  static const String adminSettingsReset = '$baseUrl/admin/settings/reset';
}
