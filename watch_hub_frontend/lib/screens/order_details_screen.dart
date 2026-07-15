import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/widgets/tracking_timeline.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/models/order.dart';

class OrderDetailsScreen extends StatelessWidget {
  final OrderModel order;

  const OrderDetailsScreen({
    super.key,
    required this.order,
  });

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dateStr = DateFormat('MMM dd, yyyy - hh:mm a').format(order.orderDate);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: Text(order.orderNumber),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppConstants.paddingMedium),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. Order Info Card
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(AppConstants.paddingMedium),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
                  border: Border.all(color: AppColors.border),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Date Placed: $dateStr', style: theme.textTheme.bodyMedium),
                    const SizedBox(height: 6.0),
                    Text('Payment: ${order.paymentMethod}', style: theme.textTheme.bodyMedium),
                    const SizedBox(height: 6.0),
                    Text('Address: ${order.shippingAddress}', style: theme.textTheme.bodyMedium),
                  ],
                ),
              ),
              AppConstants.spacingLarge,

              // 2. Tracking Timeline Section
              Text('Delivery Progress', style: theme.textTheme.titleMedium),
              const SizedBox(height: 12.0),
              TrackingTimeline(steps: order.trackingTimeline),
              const Divider(height: 32.0),

              // 3. Ordered Items List
              Text('Ordered Items', style: theme.textTheme.titleMedium),
              const SizedBox(height: 12.0),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: order.items.length,
                itemBuilder: (context, index) {
                  final item = order.items[index];
                  final watch = item.watch;

                  return Container(
                    margin: const EdgeInsets.only(bottom: AppConstants.paddingSmall),
                    padding: const EdgeInsets.all(AppConstants.paddingSmall),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      children: [
                        // Watch image
                        Container(
                          width: 60.0,
                          height: 60.0,
                          color: AppColors.surface,
                          child: WatchImage(
                            imagePath: watch.images.isNotEmpty ? watch.images.first : '',
                            fit: BoxFit.contain,
                          ),
                        ),
                        AppConstants.spacingMedium,
                        // Info Details
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                watch.model,
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                  fontSize: 14.0,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 2.0),
                              Text(
                                'Size: ${item.size}  |  Color: ${item.color}',
                                style: const TextStyle(
                                  color: AppColors.textSecondary,
                                  fontSize: 11.0,
                                ),
                              ),
                              const SizedBox(height: 4.0),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    '${item.quantity}x  @  ${AppConstants.currencySymbol}${watch.discountedPrice.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 12.0,
                                    ),
                                  ),
                                  Text(
                                    '${AppConstants.currencySymbol}${item.totalPrice.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13.0,
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
              const Divider(height: 32.0),

              // 4. Detailed Pricing Bill Card
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Subtotal', style: TextStyle(color: AppColors.textSecondary)),
                  Text(
                    '${AppConstants.currencySymbol}${order.subtotal.toStringAsFixed(2)}',
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
                    order.shipping == 0.0
                        ? 'FREE'
                        : '${AppConstants.currencySymbol}${order.shipping.toStringAsFixed(2)}',
                    style: TextStyle(
                      color: order.shipping == 0.0 ? AppColors.success : AppColors.textPrimary,
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
                    '${AppConstants.currencySymbol}${order.tax.toStringAsFixed(2)}',
                    style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold),
                  ),
                ],
              ),
              const Divider(height: 24.0),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Total Paid',
                    style: theme.textTheme.titleMedium?.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    '${AppConstants.currencySymbol}${order.grandTotal.toStringAsFixed(2)}',
                    style: theme.textTheme.titleLarge?.copyWith(
                      color: AppColors.primary,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              AppConstants.spacingLarge,
            ],
          ),
        ),
      ),
    );
  }
}
