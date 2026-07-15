import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/widgets/empty_state.dart';
import 'package:watch_hub_frontend/core/widgets/custom_button.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cartProvider = Provider.of<CartProvider>(context);
    final cartItems = cartProvider.items;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Shopping Cart'),
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: cartItems.isEmpty
            ? EmptyState(
                icon: Icons.shopping_bag_outlined,
                title: 'Your Cart is Empty',
                description: 'Looks like you haven\'t added any premium watches to your cart yet.',
                buttonLabel: 'START SHOPPING',
                onButtonPressed: () {
                  Navigator.pushNamedAndRemoveUntil(context, AppRoutes.mainLayout, (route) => false);
                },
              )
            : Column(
                children: [
                  // List of items
                  Expanded(
                    child: ListView.builder(
                      padding: const EdgeInsets.all(AppConstants.paddingMedium),
                      itemCount: cartItems.length,
                      itemBuilder: (context, index) {
                        final item = cartItems[index];
                        final watch = item.watch;

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
                              // Image
                              GestureDetector(
                                onTap: () => Navigator.pushNamed(context, AppRoutes.productDetails, arguments: watch),
                                child: Container(
                                  width: 80.0,
                                  height: 80.0,
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
                              // Info
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Expanded(
                                          child: Text(
                                            watch.model,
                                            style: theme.textTheme.titleMedium?.copyWith(
                                              fontSize: 15.0,
                                            ),
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                        IconButton(
                                          icon: const Icon(Icons.delete_outline, color: AppColors.textLight, size: 20.0),
                                          onPressed: () {
                                            cartProvider.removeItem(item.id);
                                          },
                                          constraints: const BoxConstraints(),
                                          padding: EdgeInsets.zero,
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 2.0),
                                    Text(
                                      'Size: ${item.size}  |  Color: ${item.color}',
                                      style: theme.textTheme.bodyMedium?.copyWith(
                                        fontSize: 12.0,
                                      ),
                                    ),
                                    const SizedBox(height: 8.0),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        // Price
                                        Text(
                                          '${AppConstants.currencySymbol}${watch.discountedPrice.toStringAsFixed(2)}',
                                          style: const TextStyle(
                                            color: AppColors.primary,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 15.0,
                                          ),
                                        ),
                                        // Quant controller
                                        Container(
                                          decoration: BoxDecoration(
                                            color: AppColors.surface,
                                            borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                                            border: Border.all(color: AppColors.border),
                                          ),
                                          child: Row(
                                            children: [
                                              GestureDetector(
                                                onTap: () => cartProvider.decrementQuantity(item.id),
                                                child: const Padding(
                                                  padding: EdgeInsets.all(6.0),
                                                  child: Icon(Icons.remove, size: 14.0),
                                                ),
                                              ),
                                              Padding(
                                                padding: const EdgeInsets.symmetric(horizontal: 10.0),
                                                child: Text(
                                                  item.quantity.toString(),
                                                  style: const TextStyle(
                                                    fontWeight: FontWeight.bold,
                                                    color: AppColors.primary,
                                                    fontSize: 13.0,
                                                  ),
                                                ),
                                              ),
                                              GestureDetector(
                                                onTap: () => cartProvider.incrementQuantity(item.id),
                                                child: const Padding(
                                                  padding: EdgeInsets.all(6.0),
                                                  child: Icon(Icons.add, size: 14.0),
                                                ),
                                              ),
                                            ],
                                          ),
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
                  // Bill Summary Panel
                  Container(
                    padding: const EdgeInsets.all(AppConstants.paddingLarge),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      border: Border(top: BorderSide(color: AppColors.border)),
                      boxShadow: [
                        BoxShadow(
                          color: AppColors.shadow,
                          offset: Offset(0.0, -4.0),
                          blurRadius: 10.0,
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Subtotal', style: TextStyle(color: AppColors.textSecondary)),
                            Text(
                              '${AppConstants.currencySymbol}${cartProvider.subtotal.toStringAsFixed(2)}',
                              style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8.0),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Shipping', style: TextStyle(color: AppColors.textSecondary)),
                            Text(
                              cartProvider.shipping == 0.0
                                  ? 'FREE'
                                  : '${AppConstants.currencySymbol}${cartProvider.shipping.toStringAsFixed(2)}',
                              style: TextStyle(
                                color: cartProvider.shipping == 0.0 ? AppColors.success : AppColors.textPrimary,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8.0),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Tax (8%)', style: TextStyle(color: AppColors.textSecondary)),
                            Text(
                              '${AppConstants.currencySymbol}${cartProvider.tax.toStringAsFixed(2)}',
                              style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                        const Divider(height: 24.0),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              'Total Amount',
                              style: theme.textTheme.titleMedium?.copyWith(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            Text(
                              '${AppConstants.currencySymbol}${cartProvider.grandTotal.toStringAsFixed(2)}',
                              style: theme.textTheme.titleLarge?.copyWith(
                                color: AppColors.primary,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        AppConstants.spacingLarge,
                        CustomButton(
                          label: 'PROCEED TO CHECKOUT',
                          onPressed: () {
                            Navigator.pushNamed(context, AppRoutes.checkout);
                          },
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
