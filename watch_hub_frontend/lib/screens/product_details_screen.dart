import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:dio/dio.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/network/api_config.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/core/widgets/guest_dialog.dart';
import 'package:watch_hub_frontend/models/watch.dart';
import 'package:watch_hub_frontend/models/review.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/product_provider.dart';
import 'package:watch_hub_frontend/providers/wishlist_provider.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

class ProductDetailsScreen extends StatefulWidget {
  final Watch watch;

  const ProductDetailsScreen({super.key, required this.watch});

  @override
  State<ProductDetailsScreen> createState() => _ProductDetailsScreenState();
}

class _ProductDetailsScreenState extends State<ProductDetailsScreen> {
  int _quantity = 1;
  String _selectedSize = 'Standard';
  int _activeImageIndex = 0;
  final PageController _imagePageController = PageController();
  List<Review> _reviews = [];
  bool _isLoadingReviews = true;
  bool _showAllReviews = false;

  final Dio _dio = Dio();
  final StorageService _storage = StorageService();

  @override
  void initState() {
    super.initState();

    if (widget.watch.sizes.isNotEmpty) {
      _selectedSize = widget.watch.sizes.first;
    }

    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ProductProvider>(
        context,
        listen: false,
      ).addToRecentlyViewed(widget.watch);
    });

    _fetchReviewsDirectly();
  }

  @override
  void dispose() {
    _imagePageController.dispose();
    super.dispose();
  }

  // Fetch reviews directly
  Future<void> _fetchReviewsDirectly() async {
    print('🟡 [DIRECT] Fetching reviews for watch: ${widget.watch.id}');

    setState(() {
      _isLoadingReviews = true;
    });

    try {
      final token = await _storage.getToken();
      final response = await _dio.get(
        '${ApiConfig.baseUrl}/reviews/watch/${widget.watch.id}',
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            if (token != null) 'Authorization': 'Bearer $token',
          },
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;

        final reviewsData = data['data']['reviews']['data'] as List? ?? [];

        print('🟡 [DIRECT] Reviews found: ${reviewsData.length}');

        _reviews = reviewsData
            .map((e) => Review.fromJson(e))
            .cast<Review>()
            .toList();

        setState(() {
          _isLoadingReviews = false;
        });
      } else {
        setState(() {
          _isLoadingReviews = false;
        });
      }
    } catch (e) {
      print('🔴 [DIRECT] Error: $e');
      setState(() {
        _isLoadingReviews = false;
      });
    }
  }

  void _onAddToCart() {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    cartProvider.addToCart(widget.watch, size: _selectedSize);

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('${widget.watch.model} Added to Cart'),
        duration: const Duration(seconds: 2),
        action: SnackBarAction(
          label: 'VIEW CART',
          onPressed: () {
            Navigator.pushNamed(context, AppRoutes.checkout);
          },
        ),
      ),
    );
  }

  void _onBuyNow() {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    cartProvider.addToCart(widget.watch, size: _selectedSize);
    Navigator.pushNamed(context, AppRoutes.checkout);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final wishlistProvider = Provider.of<WishlistProvider>(context);

    final isFav = wishlistProvider.isFavorite(widget.watch);
    final hasDiscount = widget.watch.discount > 0.0;
    final images = widget.watch.images.isNotEmpty
        ? widget.watch.images
        : ['https://via.placeholder.com/400x400?text=No+Image'];

    return Scaffold(
      backgroundColor: AppColors.background,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Image Gallery
                    Stack(
                      children: [
                        Container(
                          height: 320.0,
                          width: double.infinity,
                          color: AppColors.surface,
                          child: PageView.builder(
                            controller: _imagePageController,
                            onPageChanged: (index) {
                              setState(() {
                                _activeImageIndex = index;
                              });
                            },
                            itemCount: images.length,
                            itemBuilder: (context, index) {
                              return Center(
                                child: WatchImage(
                                  imagePath: images[index],
                                  fit: BoxFit.contain,
                                  width: double.infinity,
                                  height: 300.0,
                                ),
                              );
                            },
                          ),
                        ),
                        // Back Button
                        Positioned(
                          top: 16.0,
                          left: 16.0,
                          child: CircleAvatar(
                            backgroundColor: Colors.white.withOpacity(0.9),
                            child: IconButton(
                              icon: const Icon(
                                Icons.arrow_back,
                                color: AppColors.textPrimary,
                              ),
                              onPressed: () => Navigator.pop(context),
                            ),
                          ),
                        ),
                        // Favorite Button
                        Positioned(
                          top: 16.0,
                          right: 16.0,
                          child: CircleAvatar(
                            backgroundColor: Colors.white.withOpacity(0.9),
                            child: IconButton(
                              icon: Icon(
                                isFav ? Icons.favorite : Icons.favorite_border,
                                color: isFav
                                    ? AppColors.favoriteRed
                                    : AppColors.textPrimary,
                              ),
                              onPressed: () {
                                if (authProvider.isGuest) {
                                  showGuestDialog(context);
                                } else {
                                  wishlistProvider.toggleFavorite(widget.watch);
                                }
                              },
                            ),
                          ),
                        ),
                        // Image Dots
                        if (images.length > 1)
                          Positioned(
                            bottom: 16.0,
                            left: 0.0,
                            right: 0.0,
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: List.generate(
                                images.length,
                                (index) => Container(
                                  margin: const EdgeInsets.symmetric(
                                    horizontal: 4.0,
                                  ),
                                  width: _activeImageIndex == index
                                      ? 16.0
                                      : 8.0,
                                  height: 8.0,
                                  decoration: BoxDecoration(
                                    color: _activeImageIndex == index
                                        ? AppColors.primary
                                        : AppColors.textLight,
                                    borderRadius: BorderRadius.circular(4.0),
                                  ),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),

                    // Product Info
                    Padding(
                      padding: const EdgeInsets.all(AppConstants.paddingMedium),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Brand & Stock
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                widget.watch.brand.toUpperCase(),
                                style: theme.textTheme.labelLarge?.copyWith(
                                  color: AppColors.secondary,
                                  letterSpacing: 2.0,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8.0,
                                  vertical: 4.0,
                                ),
                                decoration: BoxDecoration(
                                  color: widget.watch.stock > 0
                                      ? AppColors.success.withOpacity(0.1)
                                      : AppColors.error.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4.0),
                                ),
                                child: Text(
                                  widget.watch.stock > 0
                                      ? 'IN STOCK'
                                      : 'OUT OF STOCK',
                                  style: TextStyle(
                                    color: widget.watch.stock > 0
                                        ? AppColors.success
                                        : AppColors.error,
                                    fontSize: 11.0,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6.0),

                          // Model Name
                          Text(
                            widget.watch.model,
                            style: theme.textTheme.headlineMedium?.copyWith(
                              color: AppColors.primary,
                            ),
                          ),
                          const SizedBox(height: 8.0),

                          // Price & Rating
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.baseline,
                                textBaseline: TextBaseline.alphabetic,
                                children: [
                                  Text(
                                    '\$${widget.watch.discountedPrice.toStringAsFixed(2)}',
                                    style: theme.textTheme.headlineMedium
                                        ?.copyWith(
                                          color: AppColors.primary,
                                          fontWeight: FontWeight.bold,
                                        ),
                                  ),
                                  if (hasDiscount) ...[
                                    const SizedBox(width: 8.0),
                                    Text(
                                      '\$${widget.watch.price.toStringAsFixed(2)}',
                                      style: theme.textTheme.bodyMedium
                                          ?.copyWith(
                                            color: AppColors.textLight,
                                            decoration:
                                                TextDecoration.lineThrough,
                                          ),
                                    ),
                                  ],
                                ],
                              ),
                              Row(
                                children: [
                                  const Icon(
                                    Icons.star,
                                    color: AppColors.secondary,
                                    size: 20.0,
                                  ),
                                  const SizedBox(width: 4.0),
                                  Text(
                                    widget.watch.rating.toStringAsFixed(1),
                                    style: theme.textTheme.titleMedium
                                        ?.copyWith(fontWeight: FontWeight.bold),
                                  ),
                                  const SizedBox(width: 4.0),
                                  if (_isLoadingReviews)
                                    const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                      ),
                                    )
                                  else
                                    Text(
                                      '(${_reviews.length} reviews)',
                                      style: theme.textTheme.bodyMedium,
                                    ),
                                ],
                              ),
                            ],
                          ),
                          const Divider(height: 32.0),

                          // Select Size
                          if (widget.watch.sizes.isNotEmpty) ...[
                            Text(
                              'Select Size',
                              style: theme.textTheme.titleSmall,
                            ),
                            const SizedBox(height: 8.0),
                            Wrap(
                              spacing: 8.0,
                              runSpacing: 8.0,
                              children: widget.watch.sizes.map((size) {
                                final isSelected = _selectedSize == size;
                                return GestureDetector(
                                  onTap: () =>
                                      setState(() => _selectedSize = size),
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16.0,
                                      vertical: 8.0,
                                    ),
                                    decoration: BoxDecoration(
                                      color: isSelected
                                          ? AppColors.primary
                                          : AppColors.surface,
                                      borderRadius: BorderRadius.circular(
                                        AppConstants.radiusSmall,
                                      ),
                                      border: Border.all(
                                        color: isSelected
                                            ? AppColors.primary
                                            : AppColors.border,
                                      ),
                                    ),
                                    child: Text(
                                      size,
                                      style: TextStyle(
                                        color: isSelected
                                            ? Colors.white
                                            : AppColors.textPrimary,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 13.0,
                                      ),
                                    ),
                                  ),
                                );
                              }).toList(),
                            ),
                            const Divider(height: 32.0),
                          ],

                          // Quantity
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                'Quantity',
                                style: theme.textTheme.titleSmall,
                              ),
                              Container(
                                decoration: BoxDecoration(
                                  color: AppColors.surface,
                                  borderRadius: BorderRadius.circular(
                                    AppConstants.radiusSmall,
                                  ),
                                  border: Border.all(color: AppColors.border),
                                ),
                                child: Row(
                                  children: [
                                    IconButton(
                                      icon: const Icon(
                                        Icons.remove,
                                        size: 18.0,
                                      ),
                                      onPressed: authProvider.isGuest
                                          ? null
                                          : () {
                                              if (_quantity > 1) {
                                                setState(() => _quantity--);
                                              }
                                            },
                                    ),
                                    Text(
                                      '$_quantity',
                                      style: theme.textTheme.titleMedium
                                          ?.copyWith(
                                            color: authProvider.isGuest
                                                ? AppColors.textLight
                                                : AppColors.primary,
                                          ),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.add, size: 18.0),
                                      onPressed: authProvider.isGuest
                                          ? null
                                          : () {
                                              setState(() => _quantity++);
                                            },
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const Divider(height: 32.0),

                          // Description
                          Text(
                            'Description',
                            style: theme.textTheme.titleMedium,
                          ),
                          const SizedBox(height: 8.0),
                          Text(
                            widget.watch.description,
                            style: theme.textTheme.bodyMedium?.copyWith(
                              height: 1.5,
                            ),
                          ),
                          const Divider(height: 32.0),

                          // Specifications
                          if (widget.watch.specifications.isNotEmpty) ...[
                            Text(
                              'Specifications',
                              style: theme.textTheme.titleMedium,
                            ),
                            const SizedBox(height: 12.0),
                            Container(
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(
                                  AppConstants.radiusMedium,
                                ),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: ListView.builder(
                                shrinkWrap: true,
                                physics: const NeverScrollableScrollPhysics(),
                                itemCount: widget.watch.specifications.length,
                                itemBuilder: (context, index) {
                                  String specKey = widget
                                      .watch
                                      .specifications
                                      .keys
                                      .elementAt(index);
                                  String specValue =
                                      widget.watch.specifications[specKey]!;
                                  final isLast =
                                      index ==
                                      widget.watch.specifications.length - 1;

                                  return Container(
                                    padding: const EdgeInsets.all(12.0),
                                    decoration: BoxDecoration(
                                      border: isLast
                                          ? null
                                          : const Border(
                                              bottom: BorderSide(
                                                color: AppColors.border,
                                              ),
                                            ),
                                    ),
                                    child: Row(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Expanded(
                                          flex: 2,
                                          child: Text(
                                            specKey,
                                            style: const TextStyle(
                                              color: AppColors.textSecondary,
                                              fontWeight: FontWeight.w600,
                                              fontSize: 13.0,
                                            ),
                                          ),
                                        ),
                                        Expanded(
                                          flex: 3,
                                          child: Text(
                                            specValue,
                                            style: const TextStyle(
                                              color: AppColors.textPrimary,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13.0,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  );
                                },
                              ),
                            ),
                            const Divider(height: 32.0),
                          ],

                          // =============================================
                          // REVIEWS - WITH SEE ALL / SHOW LESS
                          // =============================================
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        'Reviews (${_reviews.length})',
                                        style: theme.textTheme.titleMedium,
                                      ),
                                      if (_isLoadingReviews) ...[
                                        const SizedBox(width: 8),
                                        const SizedBox(
                                          width: 16,
                                          height: 16,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                  if (_reviews.length > 3)
                                    TextButton(
                                      onPressed: () {
                                        setState(() {
                                          _showAllReviews = !_showAllReviews;
                                        });
                                      },
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Text(
                                            _showAllReviews
                                                ? 'Show Less'
                                                : 'See All',
                                          ),
                                          Icon(
                                            _showAllReviews
                                                ? Icons.keyboard_arrow_up
                                                : Icons.keyboard_arrow_down,
                                            size: 18,
                                          ),
                                        ],
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 12.0),

                              if (_isLoadingReviews) ...[
                                const Padding(
                                  padding: EdgeInsets.symmetric(vertical: 20.0),
                                  child: Center(
                                    child: CircularProgressIndicator(),
                                  ),
                                ),
                              ] else if (_reviews.isEmpty) ...[
                                const Padding(
                                  padding: EdgeInsets.symmetric(vertical: 20.0),
                                  child: Center(
                                    child: Text(
                                      'No reviews yet. Be the first to review!',
                                      style: TextStyle(
                                        color: AppColors.textSecondary,
                                      ),
                                    ),
                                  ),
                                ),
                              ] else ...[
                                ...(_showAllReviews
                                        ? _reviews
                                        : _reviews.take(3).toList())
                                    .map((review) {
                                      return Padding(
                                        padding: const EdgeInsets.only(
                                          bottom: AppConstants.paddingMedium,
                                        ),
                                        child: Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          children: [
                                            Row(
                                              mainAxisAlignment:
                                                  MainAxisAlignment
                                                      .spaceBetween,
                                              children: [
                                                Text(
                                                  review.userName,
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    color:
                                                        AppColors.textPrimary,
                                                  ),
                                                ),
                                                Row(
                                                  children: List.generate(5, (
                                                    starIdx,
                                                  ) {
                                                    return Icon(
                                                      starIdx <
                                                              review.rating
                                                                  .ceil()
                                                          ? Icons.star
                                                          : Icons.star_border,
                                                      color:
                                                          AppColors.secondary,
                                                      size: 14.0,
                                                    );
                                                  }),
                                                ),
                                              ],
                                            ),
                                            const SizedBox(height: 4.0),
                                            Text(
                                              review.comment,
                                              style: theme.textTheme.bodyMedium,
                                            ),
                                            const Divider(height: 24.0),
                                          ],
                                        ),
                                      );
                                    })
                                    .toList(),

                                // Show Less at bottom when expanded
                                if (_showAllReviews && _reviews.length > 3)
                                  Center(
                                    child: TextButton(
                                      onPressed: () {
                                        setState(() {
                                          _showAllReviews = false;
                                        });
                                      },
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: const [
                                          Icon(
                                            Icons.keyboard_arrow_up,
                                            size: 18,
                                          ),
                                          SizedBox(width: 4),
                                          Text('Show Less'),
                                        ],
                                      ),
                                    ),
                                  ),
                              ],
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Bottom Buttons
            Container(
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              decoration: const BoxDecoration(
                color: Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: AppColors.shadow,
                    offset: Offset(0.0, -4.0),
                    blurRadius: 10.0,
                  ),
                ],
              ),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: widget.watch.stock > 0
                          ? () {
                              if (authProvider.isGuest) {
                                showGuestDialog(context);
                              } else {
                                _onAddToCart();
                              }
                            }
                          : null,
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(
                          color: AppColors.primary,
                          width: 1.5,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            AppConstants.radiusSmall,
                          ),
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 14.0),
                      ),
                      child: Text(
                        'ADD TO CART',
                        style: theme.textTheme.titleMedium?.copyWith(
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 16.0),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: widget.watch.stock > 0
                          ? () {
                              if (authProvider.isGuest) {
                                showGuestDialog(context);
                              } else {
                                _onBuyNow();
                              }
                            }
                          : null,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            AppConstants.radiusSmall,
                          ),
                        ),
                        padding: const EdgeInsets.symmetric(vertical: 14.0),
                      ),
                      child: Text(
                        'BUY NOW',
                        style: theme.textTheme.titleMedium?.copyWith(
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
