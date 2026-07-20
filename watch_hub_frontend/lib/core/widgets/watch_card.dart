import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/core/widgets/guest_dialog.dart';
import 'package:watch_hub_frontend/models/watch.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/wishlist_provider.dart';

class WatchCard extends StatelessWidget {
  final Watch watch;

  const WatchCard({super.key, required this.watch});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final wishlistProvider = Provider.of<WishlistProvider>(context);
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final isFav = wishlistProvider.isFavorite(watch);

    final bool hasDiscount = watch.discount > 0.0;

    return GestureDetector(
      onTap: () {
        Navigator.pushNamed(
          context,
          AppRoutes.productDetails,
          arguments: watch,
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          border: Border.all(color: AppColors.border, width: 1.0),
          boxShadow: const [
            BoxShadow(
              color: AppColors.shadow,
              offset: Offset(0.0, 4.0),
              blurRadius: 10.0,
            ),
          ],
        ),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          child: Stack(
            children: [
              // Main content
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Product Image
                  Expanded(
                    child: Container(
                      width: double.infinity,
                      color: AppColors.surface,
                      child: Hero(
                        tag: 'watch-hero-${watch.id}-${UniqueKey().toString()}',
                        child: WatchImage(
                          imagePath: watch.images.isNotEmpty
                              ? watch.images.first
                              : '',
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                  ),
                  // Product Details
                  Padding(
                    padding: const EdgeInsets.all(AppConstants.paddingSmall),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Brand
                        Text(
                          watch.brand.toUpperCase(),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: theme.textTheme.labelMedium?.copyWith(
                            color: AppColors.secondary,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 1.0,
                          ),
                        ),
                        const SizedBox(height: 2.0),
                        // Model Name
                        Text(
                          watch.model,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: theme.textTheme.bodyMedium?.copyWith(
                            color: AppColors.textPrimary,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        const SizedBox(height: 4.0),
                        // Rating Row
                        Row(
                          children: [
                            const Icon(
                              Icons.star,
                              color: AppColors.secondary,
                              size: 14.0,
                            ),
                            const SizedBox(width: 4.0),
                            Text(
                              watch.rating.toStringAsFixed(1),
                              style: theme.textTheme.labelSmall?.copyWith(
                                color: AppColors.textPrimary,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(width: 4.0),
                            Text(
                              '(${watch.ratingCount ?? 0})',
                              style: theme.textTheme.labelSmall?.copyWith(
                                color: AppColors.textLight,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6.0),
                        // Price and Cart Button Row
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            // Price
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  if (hasDiscount) ...[
                                    Text(
                                      '${AppConstants.currencySymbol}${watch.price.toStringAsFixed(2)}',
                                      style: theme.textTheme.labelSmall
                                          ?.copyWith(
                                            color: AppColors.textLight,
                                            decoration:
                                                TextDecoration.lineThrough,
                                          ),
                                    ),
                                    Text(
                                      '${AppConstants.currencySymbol}${watch.discountedPrice.toStringAsFixed(2)}',
                                      style: theme.textTheme.bodyMedium
                                          ?.copyWith(
                                            color: AppColors.primary,
                                            fontWeight: FontWeight.bold,
                                          ),
                                    ),
                                  ] else ...[
                                    Text(
                                      '${AppConstants.currencySymbol}${watch.price.toStringAsFixed(2)}',
                                      style: theme.textTheme.bodyMedium
                                          ?.copyWith(
                                            color: AppColors.primary,
                                            fontWeight: FontWeight.bold,
                                          ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                            // Quick Add to Cart Button
                            GestureDetector(
                              onTap: () {
                                final auth = Provider.of<AuthProvider>(
                                  context,
                                  listen: false,
                                );
                                if (auth.isGuest) {
                                  showGuestDialog(context);
                                } else {
                                  final color = watch.colors.isNotEmpty
                                      ? watch.colors.first
                                      : 'Default';
                                  final size = watch.sizes.isNotEmpty
                                      ? watch.sizes.first
                                      : 'Standard';
                                  cartProvider.addToCart(watch, size: size);
                                  ScaffoldMessenger.of(context).showSnackBar(
                                    SnackBar(
                                      content: Text(
                                        '${watch.model} added to Cart',
                                      ),
                                      duration: const Duration(seconds: 2),
                                      action: SnackBarAction(
                                        label: 'VIEW CART',
                                        textColor: AppColors.secondary,
                                        onPressed: () {
                                          // Navigate to Cart tab in main layout (which is index 2)
                                          // We can pop back to main and trigger route switch or notify
                                        },
                                      ),
                                    ),
                                  );
                                }
                              },
                              child: Container(
                                padding: const EdgeInsets.all(6.0),
                                decoration: const BoxDecoration(
                                  color: AppColors.primary,
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(
                                  Icons.add_shopping_cart,
                                  color: Colors.white,
                                  size: 16.0,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              // Wishlist/Favorite Overlay Button
              Positioned(
                top: 8.0,
                right: 8.0,
                child: GestureDetector(
                  onTap: () {
                    final auth = Provider.of<AuthProvider>(
                      context,
                      listen: false,
                    );
                    if (auth.isGuest) {
                      showGuestDialog(context);
                    } else {
                      wishlistProvider.toggleFavorite(watch);
                    }
                  },
                  child: Container(
                    padding: const EdgeInsets.all(6.0),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.shadow,
                          blurRadius: 4.0,
                          offset: Offset(0.0, 2.0),
                        ),
                      ],
                    ),
                    child: Icon(
                      isFav ? Icons.favorite : Icons.favorite_border,
                      color: isFav
                          ? AppColors.favoriteRed
                          : AppColors.textLight,
                      size: 18.0,
                    ),
                  ),
                ),
              ),
              // Discount Badge Overlay
              if (hasDiscount)
                Positioned(
                  top: 8.0,
                  left: 8.0,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8.0,
                      vertical: 4.0,
                    ),
                    decoration: BoxDecoration(
                      color: AppColors.secondary,
                      borderRadius: BorderRadius.circular(
                        AppConstants.radiusXS,
                      ),
                    ),
                    child: Text(
                      '-${(watch.discount * 100).toStringAsFixed(0)}%',
                      style: theme.textTheme.labelSmall?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ),
      ),
    );
  }
}
