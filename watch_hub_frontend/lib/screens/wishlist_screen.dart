import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/widgets/empty_state.dart';
import 'package:watch_hub_frontend/core/widgets/shimmer_loader.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/wishlist_provider.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';

class WishlistScreen extends StatelessWidget {
  const WishlistScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final wishlistProvider = Provider.of<WishlistProvider>(context);
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final favorites = wishlistProvider.favorites;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Wishlist'),
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => wishlistProvider.refreshWishlist(),
          color: AppColors.primary,
          child: wishlistProvider.isLoading
              ? ShimmerLoader.buildListLoader()
              : favorites.isEmpty
                  ? EmptyState(
                      icon: Icons.favorite_border_rounded,
                      title: 'Your Wishlist is Empty',
                      description: 'Explore our premium watches and tap the heart icon to save your favorites here.',
                      buttonLabel: 'EXPLORE WATCHES',
                      onButtonPressed: () {
                        // Switch to Home tab (index 0) in main layout
                        // But since we are in bottom bar, we can navigate or pop to home
                        Navigator.pushNamedAndRemoveUntil(context, AppRoutes.mainLayout, (route) => false);
                      },
                    )
                  : ListView.builder(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(AppConstants.paddingMedium),
                      itemCount: favorites.length,
                      itemBuilder: (context, index) {
                        final watch = favorites[index];
                        final hasDiscount = watch.discount > 0.0;

                        return Container(
                          margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
                          padding: const EdgeInsets.all(AppConstants.paddingSmall),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
                            border: Border.all(color: AppColors.border),
                            boxShadow: const [
                              BoxShadow(
                                color: AppColors.shadow,
                                offset: Offset(0.0, 2.0),
                                blurRadius: 6.0,
                              ),
                            ],
                          ),
                          child: Row(
                            children: [
                              // Watch Image
                              GestureDetector(
                                onTap: () => Navigator.pushNamed(context, AppRoutes.productDetails, arguments: watch),
                                child: Container(
                                  width: 90.0,
                                  height: 90.0,
                                  decoration: BoxDecoration(
                                    color: AppColors.surface,
                                    borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                                  ),
                                  child: WatchImage(
                                    imagePath: watch.images.isNotEmpty ? watch.images.first : '',
                                    fit: BoxFit.contain,
                                  ),
                                ),
                              ),
                              AppConstants.spacingMedium,
                              // Info Details
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      watch.brand.toUpperCase(),
                                      style: theme.textTheme.labelMedium?.copyWith(
                                        color: AppColors.secondary,
                                        fontWeight: FontWeight.bold,
                                      ),
                                    ),
                                    const SizedBox(height: 2.0),
                                    Text(
                                      watch.model,
                                      style: theme.textTheme.titleMedium?.copyWith(
                                        fontSize: 15.0,
                                      ),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    const SizedBox(height: 4.0),
                                    // Prices
                                    Row(
                                      children: [
                                        Text(
                                          '${AppConstants.currencySymbol}${watch.discountedPrice.toStringAsFixed(2)}',
                                          style: const TextStyle(
                                            color: AppColors.primary,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14.0,
                                          ),
                                        ),
                                        if (hasDiscount) ...[
                                          const SizedBox(width: 6.0),
                                          Text(
                                            '${AppConstants.currencySymbol}${watch.price.toStringAsFixed(2)}',
                                            style: const TextStyle(
                                              color: AppColors.textLight,
                                              decoration: TextDecoration.lineThrough,
                                              fontSize: 12.0,
                                            ),
                                          ),
                                        ],
                                      ],
                                    ),
                                    const SizedBox(height: 8.0),
                                    // Action buttons row
                                    Row(
                                      children: [
                                        // Move to Cart
                                        SizedBox(
                                          height: 32.0,
                                          child: ElevatedButton(
                                            onPressed: () {
                                              final color = watch.colors.isNotEmpty ? watch.colors.first : 'Default';
                                              final size = watch.sizes.isNotEmpty ? watch.sizes.first : 'Standard';
                                              cartProvider.addToCart(watch, color: color, size: size);
                                              wishlistProvider.removeFavorite(watch);

                                              ScaffoldMessenger.of(context).showSnackBar(
                                                SnackBar(
                                                  content: Text('${watch.model} Moved to Cart'),
                                                ),
                                              );
                                            },
                                            style: ElevatedButton.styleFrom(
                                              backgroundColor: AppColors.primary,
                                              padding: const EdgeInsets.symmetric(horizontal: 12.0),
                                              shape: RoundedRectangleBorder(
                                                borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                                              ),
                                            ),
                                            child: const Text(
                                              'MOVE TO CART',
                                              style: TextStyle(fontSize: 11.0, color: Colors.white),
                                            ),
                                          ),
                                        ),
                                        const SizedBox(width: 8.0),
                                        // Remove
                                        IconButton(
                                          icon: const Icon(Icons.delete_outline, color: AppColors.textLight),
                                          onPressed: () {
                                            wishlistProvider.removeFavorite(watch);
                                            ScaffoldMessenger.of(context).showSnackBar(
                                              SnackBar(
                                                content: Text('${watch.model} Removed from Wishlist'),
                                              ),
                                            );
                                          },
                                          constraints: const BoxConstraints(),
                                          padding: EdgeInsets.zero,
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        );
                      },
                    ),
        ),
      ),
    );
  }
}
