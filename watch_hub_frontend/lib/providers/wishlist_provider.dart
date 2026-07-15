import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/models/watch.dart';

class WishlistProvider extends ChangeNotifier {
  final List<Watch> _favorites = [];
  bool _isLoading = false;

  List<Watch> get favorites => _favorites;
  bool get isLoading => _isLoading;

  bool isFavorite(Watch watch) {
    return _favorites.any((w) => w.id == watch.id);
  }

  void toggleFavorite(Watch watch) {
    final index = _favorites.indexWhere((w) => w.id == watch.id);
    if (index >= 0) {
      _favorites.removeAt(index);
    } else {
      _favorites.add(watch);
    }
    notifyListeners();
  }

  void removeFavorite(Watch watch) {
    _favorites.removeWhere((w) => w.id == watch.id);
    notifyListeners();
  }

  // Simulate pull-to-refresh
  Future<void> refreshWishlist() async {
    _isLoading = true;
    notifyListeners();

    // Simulate network delay
    await Future.delayed(const Duration(milliseconds: 1000));

    _isLoading = false;
    notifyListeners();
  }
}
