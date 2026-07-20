import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/models/cart_item.dart';
import 'package:watch_hub_frontend/models/watch.dart';

class CartProvider extends ChangeNotifier {
  final List<CartItem> _items = [];

  List<CartItem> get items => _items;

  int get itemCount => _items.fold(0, (sum, item) => sum + item.quantity);

  // Add Item to Cart
  void addToCart(Watch watch, { required String size}) {
    // Check if the exact product with same color and size already exists in the cart
    final existingIndex = _items.indexWhere(
      (item) => item.watch.id == watch.id && item.size == size,
    );

    if (existingIndex >= 0) {
      _items[existingIndex].quantity += 1;
    } else {
      _items.add(
        CartItem(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          watch: watch,
          // color: color,
          size: size,
          quantity: 1,
        ),
      );
    }
    notifyListeners();
  }

  // Remove Item
  void removeItem(String id) {
    _items.removeWhere((item) => item.id == id);
    notifyListeners();
  }

  // Increment Quantity
  void incrementQuantity(String id) {
    final index = _items.indexWhere((item) => item.id == id);
    if (index >= 0) {
      _items[index].quantity += 1;
      notifyListeners();
    }
  }

  // Decrement Quantity
  void decrementQuantity(String id) {
    final index = _items.indexWhere((item) => item.id == id);
    if (index >= 0) {
      if (_items[index].quantity > 1) {
        _items[index].quantity -= 1;
      } else {
        _items.removeAt(index);
      }
      notifyListeners();
    }
  }

  // Clear Cart
  void clear() {
    _items.clear();
    notifyListeners();
  }

  // Pricing Calculations
  double get subtotal => _items.fold(0.0, (sum, item) => sum + item.totalPrice);
  
  double get shipping {
    if (subtotal == 0.0) return 0.0;
    return subtotal >= 1000.0 ? 0.0 : 25.0; // Free shipping for premium orders above $1000
  }
  
  double get tax => subtotal * 0.08; // 8% sales tax

  double get grandTotal => subtotal + shipping + tax;
}
