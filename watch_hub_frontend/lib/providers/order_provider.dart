import 'dart:math';
import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/models/cart_item.dart';
import 'package:watch_hub_frontend/models/order.dart';

class OrderProvider extends ChangeNotifier {
  final List<OrderModel> _orders = [];
  bool _isLoading = false;

  List<OrderModel> get orders => _orders;
  bool get isLoading => _isLoading;

  // Retrieve orders by specific status
  List<OrderModel> getOrdersByStatus(OrderStatus status) {
    return _orders.where((o) => o.status == status).toList();
  }

  // Place Order logic
  Future<OrderModel> placeOrder({
    required List<CartItem> items,
    required double subtotal,
    required double shipping,
    required double tax,
    required double grandTotal,
    required String address,
    required String paymentMethod,
  }) async {
    _isLoading = true;
    notifyListeners();

    // Simulate database delay
    await Future.delayed(const Duration(milliseconds: 1500));

    final orderNo = 'WH-${Random().nextInt(90000) + 10000}';
    final now = DateTime.now();

    final order = OrderModel(
      id: now.millisecondsSinceEpoch.toString(),
      orderNumber: orderNo,
      items: List.from(items),
      subtotal: subtotal,
      shipping: shipping,
      tax: tax,
      grandTotal: grandTotal,
      status: OrderStatus.pending,
      shippingAddress: address,
      paymentMethod: paymentMethod,
      orderDate: now,
      trackingTimeline: [
        TrackingStep(
          title: 'Order Placed',
          description: 'Your order was successfully received and is awaiting processing.',
          time: now,
          isCompleted: true,
        ),
        TrackingStep(
          title: 'Processing',
          description: 'We are packaging your watch and doing quality control checks.',
          time: now.add(const Duration(minutes: 30)),
          isCompleted: false,
        ),
        TrackingStep(
          title: 'Shipped',
          description: 'Your package is handed over to our premium courier partner.',
          time: now.add(const Duration(hours: 4)),
          isCompleted: false,
        ),
        TrackingStep(
          title: 'Delivered',
          description: 'Package has been signed and delivered to your address.',
          time: now.add(const Duration(days: 2)),
          isCompleted: false,
        ),
      ],
    );

    _orders.insert(0, order);
    _isLoading = false;
    notifyListeners();
    return order;
  }

  // Simulate pull-to-refresh
  Future<void> refreshOrders() async {
    _isLoading = true;
    notifyListeners();

    // Simulate network delay
    await Future.delayed(const Duration(milliseconds: 1000));

    _isLoading = false;
    notifyListeners();
  }

  // Helper function to simulate delivery updates for testing
  void simulateStatusUpdate(String orderId, OrderStatus newStatus) {
    final index = _orders.indexWhere((o) => o.id == orderId);
    if (index >= 0) {
      final order = _orders[index];
      
      // Update tracking timeline steps based on status
      final updatedTimeline = order.trackingTimeline.map((step) {
        bool isStepCompleted = step.isCompleted;
        if (newStatus == OrderStatus.processing) {
          if (step.title == 'Processing') isStepCompleted = true;
        } else if (newStatus == OrderStatus.delivered) {
          isStepCompleted = true; // All steps completed
        }
        return TrackingStep(
          title: step.title,
          description: step.description,
          time: step.time,
          isCompleted: isStepCompleted,
        );
      }).toList();

      _orders[index] = OrderModel(
        id: order.id,
        orderNumber: order.orderNumber,
        items: order.items,
        subtotal: order.subtotal,
        shipping: order.shipping,
        tax: order.tax,
        grandTotal: order.grandTotal,
        status: newStatus,
        shippingAddress: order.shippingAddress,
        paymentMethod: order.paymentMethod,
        orderDate: order.orderDate,
        trackingTimeline: updatedTimeline,
      );
      notifyListeners();
    }
  }
}
