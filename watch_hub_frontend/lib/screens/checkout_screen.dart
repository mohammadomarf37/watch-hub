import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/core/widgets/custom_button.dart';
import 'package:watch_hub_frontend/core/widgets/custom_text_field.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/order_provider.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _addressController = TextEditingController();
  String _paymentMethod = 'Credit Card'; // Credit Card, Cash on Delivery
  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    final auth = Provider.of<AuthProvider>(context, listen: false);
    _addressController.text = auth.currentUser?.address ?? '';
  }

  @override
  void dispose() {
    _addressController.dispose();
    super.dispose();
  }

  void _onPlaceOrder() async {
    if (_formKey.currentState!.validate()) {
      final cartProvider = Provider.of<CartProvider>(context, listen: false);
      final orderProvider = Provider.of<OrderProvider>(context, listen: false);

      final placedOrder = await orderProvider.placeOrder(
        items: cartProvider.items,
        subtotal: cartProvider.subtotal,
        shipping: cartProvider.shipping,
        tax: cartProvider.tax,
        grandTotal: cartProvider.grandTotal,
        address: _addressController.text.trim(),
        paymentMethod: _paymentMethod,
      );

      // Clear Cart
      cartProvider.clear();

      if (mounted) {
        _showSuccessDialog(placedOrder);
      }
    }
  }

  void _showSuccessDialog(dynamic placedOrder) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) {
        final theme = Theme.of(context);
        return Dialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
          ),
          child: Padding(
            padding: const EdgeInsets.all(AppConstants.paddingLarge),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Animated green checkmark
                Container(
                  width: 80.0,
                  height: 80.0,
                  decoration: const BoxDecoration(
                    color: AppColors.success,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check,
                    color: Colors.white,
                    size: 40.0,
                  ),
                ),
                AppConstants.spacingLarge,
                Text(
                  'Order Placed!',
                  style: theme.textTheme.titleLarge?.copyWith(
                    color: AppColors.primary,
                  ),
                ),
                const SizedBox(height: 8.0),
                const Text(
                  'Your order has been placed successfully and is now being processed.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: AppColors.textSecondary,
                    height: 1.4,
                  ),
                ),
                AppConstants.spacingLarge,
                // View details
                CustomButton(
                  label: 'TRACK ORDER',
                  onPressed: () {
                    Navigator.pop(context); // close dialog
                    Navigator.pushReplacementNamed(
                      context,
                      AppRoutes.orderDetails,
                      arguments: placedOrder,
                    );
                  },
                ),
                const SizedBox(height: 12.0),
                CustomButton(
                  label: 'CONTINUE SHOPPING',
                  isOutlined: true,
                  onPressed: () {
                    Navigator.pop(context); // close dialog
                    Navigator.pushNamedAndRemoveUntil(
                      context,
                      AppRoutes.mainLayout,
                      (route) => false,
                    );
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final cartProvider = Provider.of<CartProvider>(context);
    final orderProvider = Provider.of<OrderProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Checkout'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(AppConstants.paddingMedium),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // 1. Shipping Address Section
                      Text('Shipping Details', style: theme.textTheme.titleMedium),
                      AppConstants.spacingSmall,
                      CustomTextField(
                        controller: _addressController,
                        label: 'Delivery Address',
                        hintText: 'Enter your full shipping address',
                        prefixIcon: Icons.location_on_outlined,
                        maxLines: 2,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) {
                            return 'Shipping address is required';
                          }
                          return null;
                        },
                      ),
                      const Divider(height: 32.0),

                      // 2. Payment Method Section
                      Text('Payment Method', style: theme.textTheme.titleMedium),
                      AppConstants.spacingSmall,
                      Container(
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
                          border: Border.all(color: AppColors.border),
                        ),
                        child: Column(
                          children: [
                            RadioListTile<String>(
                              title: const Text(
                                'Credit / Debit Card',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              subtitle: const Text('Pay securely using card details'),
                              value: 'Credit Card',
                              groupValue: _paymentMethod,
                              activeColor: AppColors.primary,
                              onChanged: (val) {
                                setState(() {
                                  _paymentMethod = val!;
                                });
                              },
                            ),
                            const Divider(height: 1.0),
                            RadioListTile<String>(
                              title: const Text(
                                'Cash on Delivery (COD)',
                                style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.textPrimary,
                                ),
                              ),
                              subtitle: const Text('Pay with cash when package arrives'),
                              value: 'Cash on Delivery',
                              groupValue: _paymentMethod,
                              activeColor: AppColors.primary,
                              onChanged: (val) {
                                setState(() {
                                  _paymentMethod = val!;
                                });
                              },
                            ),
                          ],
                        ),
                      ),
                      const Divider(height: 32.0),

                      // 3. Order Summary Items list
                      Text('Order Summary', style: theme.textTheme.titleMedium),
                      AppConstants.spacingSmall,
                      ListView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: cartProvider.items.length,
                        itemBuilder: (context, index) {
                          final item = cartProvider.items[index];
                          return Padding(
                            padding: const EdgeInsets.symmetric(vertical: 4.0),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    '${item.watch.model} (${item.quantity}x)',
                                    style: const TextStyle(
                                      color: AppColors.textSecondary,
                                      fontSize: 13.0,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                Text(
                                  '${AppConstants.currencySymbol}${item.totalPrice.toStringAsFixed(2)}',
                                  style: const TextStyle(
                                    color: AppColors.textPrimary,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13.0,
                                  ),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                      const Divider(height: 32.0),
                    ],
                  ),
                ),
              ),
              // Bottom Placement Panel
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
                        Text(
                          'Total Payable',
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
                      label: 'PLACE ORDER',
                      isLoading: orderProvider.isLoading,
                      onPressed: _onPlaceOrder,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
