import 'package:watch_hub_frontend/models/watch.dart';

class CartItem {
  final String id;
  final Watch watch;
  final String color;
  final String size;
  int quantity;

  CartItem({
    required this.id,
    required this.watch,
    required this.color,
    required this.size,
    this.quantity = 1,
  });

  double get totalPrice => watch.discountedPrice * quantity;
}
