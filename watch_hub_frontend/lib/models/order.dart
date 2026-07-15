import 'package:watch_hub_frontend/models/cart_item.dart';

enum OrderStatus {
  pending,
  processing,
  delivered,
  cancelled,
}

class TrackingStep {
  final String title;
  final String description;
  final DateTime time;
  final bool isCompleted;

  TrackingStep({
    required this.title,
    required this.description,
    required this.time,
    required this.isCompleted,
  });
}

class OrderModel {
  final String id;
  final String orderNumber;
  final List<CartItem> items;
  final double subtotal;
  final double shipping;
  final double tax;
  final double grandTotal;
  OrderStatus status;
  final String shippingAddress;
  final String paymentMethod;
  final DateTime orderDate;
  final List<TrackingStep> trackingTimeline;

  OrderModel({
    required this.id,
    required this.orderNumber,
    required this.items,
    required this.subtotal,
    required this.shipping,
    required this.tax,
    required this.grandTotal,
    required this.status,
    required this.shippingAddress,
    required this.paymentMethod,
    required this.orderDate,
    required this.trackingTimeline,
  });
}
