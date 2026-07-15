import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/core/widgets/empty_state.dart';
import 'package:watch_hub_frontend/core/widgets/shimmer_loader.dart';
import 'package:watch_hub_frontend/models/order.dart';
import 'package:watch_hub_frontend/providers/order_provider.dart';

class OrdersScreen extends StatelessWidget {
  const OrdersScreen({super.key});

  Color _getStatusColor(OrderStatus status) {
    switch (status) {
      case OrderStatus.pending:
        return AppColors.info;
      case OrderStatus.processing:
        return AppColors.warning;
      case OrderStatus.delivered:
        return AppColors.success;
      case OrderStatus.cancelled:
        return AppColors.error;
    }
  }

  String _getStatusText(OrderStatus status) {
    switch (status) {
      case OrderStatus.pending:
        return 'Pending';
      case OrderStatus.processing:
        return 'Processing';
      case OrderStatus.delivered:
        return 'Delivered';
      case OrderStatus.cancelled:
        return 'Cancelled';
    }
  }

  @override
  Widget build(BuildContext context) {
    final orderProvider = Provider.of<OrderProvider>(context);
    final allOrders = orderProvider.orders;

    return DefaultTabController(
      length: 5,
      child: Scaffold(
        backgroundColor: AppColors.background,
        appBar: AppBar(
          title: const Text('My Orders'),
          automaticallyImplyLeading: false,
          bottom: TabBar(
            isScrollable: true,
            tabAlignment: TabAlignment.start,
            indicatorColor: AppColors.primary,
            labelColor: AppColors.primary,
            unselectedLabelColor: AppColors.textSecondary,
            tabs: const [
              Tab(text: 'All'),
              Tab(text: 'Pending'),
              Tab(text: 'Processing'),
              Tab(text: 'Delivered'),
              Tab(text: 'Cancelled'),
            ],
          ),
        ),
        body: SafeArea(
          child: TabBarView(
            children: [
              _buildOrdersList(context, allOrders, orderProvider),
              _buildOrdersList(context, orderProvider.getOrdersByStatus(OrderStatus.pending), orderProvider),
              _buildOrdersList(context, orderProvider.getOrdersByStatus(OrderStatus.processing), orderProvider),
              _buildOrdersList(context, orderProvider.getOrdersByStatus(OrderStatus.delivered), orderProvider),
              _buildOrdersList(context, orderProvider.getOrdersByStatus(OrderStatus.cancelled), orderProvider),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildOrdersList(
    BuildContext context,
    List<OrderModel> ordersList,
    OrderProvider provider,
  ) {
    final theme = Theme.of(context);

    return RefreshIndicator(
      onRefresh: () => provider.refreshOrders(),
      color: AppColors.primary,
      child: provider.isLoading
          ? ShimmerLoader.buildListLoader()
          : ordersList.isEmpty
              ? EmptyState(
                  icon: Icons.receipt_long_outlined,
                  title: 'No Orders Placed',
                  description: 'You haven\'t ordered any items yet. Start shopping to create orders.',
                  buttonLabel: 'BROWSE WATCHES',
                  onButtonPressed: () {
                    Navigator.pushNamedAndRemoveUntil(context, AppRoutes.mainLayout, (route) => false);
                  },
                )
              : ListView.builder(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  itemCount: ordersList.length,
                  itemBuilder: (context, index) {
                    final order = ordersList[index];
                    final dateStr = DateFormat('MMM dd, yyyy').format(order.orderDate);
                    final statusColor = _getStatusColor(order.status);
                    final statusText = _getStatusText(order.status);

                    // Compile items overview text
                    final itemsText = order.items.map((e) => e.watch.model).join(', ');

                    return Container(
                      margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
                      padding: const EdgeInsets.all(AppConstants.paddingMedium),
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
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Header (Order number & Status chip)
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                order.orderNumber,
                                style: theme.textTheme.titleMedium?.copyWith(
                                  color: AppColors.primary,
                                ),
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8.0, vertical: 4.0),
                                decoration: BoxDecoration(
                                  color: statusColor.withOpacity(0.1),
                                  borderRadius: BorderRadius.circular(4.0),
                                ),
                                child: Text(
                                  statusText.toUpperCase(),
                                  style: TextStyle(
                                    color: statusColor,
                                    fontSize: 11.0,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4.0),
                          Text(
                            'Placed on $dateStr',
                            style: const TextStyle(
                              color: AppColors.textLight,
                              fontSize: 12.0,
                            ),
                          ),
                          const Divider(height: 24.0),
                          // Items summary overview
                          Text(
                            itemsText,
                            style: theme.textTheme.bodyMedium?.copyWith(
                              color: AppColors.textPrimary,
                              fontWeight: FontWeight.w500,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 12.0),
                          // Pricing details & Track Order Button
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Total Amount',
                                    style: TextStyle(
                                      color: AppColors.textLight,
                                      fontSize: 12.0,
                                    ),
                                  ),
                                  Text(
                                    '${AppConstants.currencySymbol}${order.grandTotal.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 15.0,
                                    ),
                                  ),
                                ],
                              ),
                              // Track Order Button
                              SizedBox(
                                height: 36.0,
                                child: ElevatedButton(
                                  onPressed: () {
                                    Navigator.pushNamed(
                                      context,
                                      AppRoutes.orderDetails,
                                      arguments: order,
                                    );
                                  },
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: AppColors.primary,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                                    ),
                                  ),
                                  child: const Text(
                                    'TRACK ORDER',
                                    style: TextStyle(fontSize: 12.0, color: Colors.white),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    );
                  },
                ),
    );
  }
}
