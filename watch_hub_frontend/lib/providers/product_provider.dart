import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/core/constants/dummy_data.dart';
import 'package:watch_hub_frontend/models/watch.dart';

class ProductProvider extends ChangeNotifier {
  final List<Watch> _allWatches = List.from(DummyData.watches);
  final List<Watch> _recentlyViewed = [];
  bool _isLoading = false;

  // Filter States
  String _searchQuery = '';
  List<String> _selectedBrands = [];
  List<String> _selectedCategories = [];
  RangeValues _priceRange = const RangeValues(0.0, 15000.0);
  double _minRating = 0.0;
  String _sortBy = 'Popularity'; // Popularity, Newest, Price Low -> High, Price High -> Low

  // Getters
  List<Watch> get allWatches => _allWatches;
  List<Watch> get recentlyViewed => _recentlyViewed;
  bool get isLoading => _isLoading;

  String get searchQuery => _searchQuery;
  List<String> get selectedBrands => _selectedBrands;
  List<String> get selectedCategories => _selectedCategories;
  RangeValues get priceRange => _priceRange;
  double get minRating => _minRating;
  String get sortBy => _sortBy;

  // Categories & Brands lists derived from data
  List<String> get brands => _allWatches.map((w) => w.brand).toSet().toList();
  List<String> get categories => _allWatches.map((w) => w.category).toSet().toList();

  // Reset Filters
  void resetFilters() {
    _searchQuery = '';
    _selectedBrands = [];
    _selectedCategories = [];
    _priceRange = const RangeValues(0.0, 15000.0);
    _minRating = 0.0;
    _sortBy = 'Popularity';
    notifyListeners();
  }

  // Setters that trigger re-render
  void setSearchQuery(String query) {
    _searchQuery = query;
    notifyListeners();
  }

  void toggleBrand(String brand) {
    if (_selectedBrands.contains(brand)) {
      _selectedBrands.remove(brand);
    } else {
      _selectedBrands.add(brand);
    }
    notifyListeners();
  }

  void toggleCategory(String category) {
    if (_selectedCategories.contains(category)) {
      _selectedCategories.remove(category);
    } else {
      _selectedCategories.add(category);
    }
    notifyListeners();
  }

  void setPriceRange(RangeValues values) {
    _priceRange = values;
    notifyListeners();
  }

  void setMinRating(double rating) {
    _minRating = rating;
    notifyListeners();
  }

  void setSortBy(String sortOption) {
    _sortBy = sortOption;
    notifyListeners();
  }

  // Filter & Sort Logic
  List<Watch> get filteredWatches {
    List<Watch> list = List.from(_allWatches);

    // Apply Search Query
    if (_searchQuery.isNotEmpty) {
      final query = _searchQuery.toLowerCase();
      list = list.where((w) {
        return w.brand.toLowerCase().contains(query) ||
            w.model.toLowerCase().contains(query) ||
            w.category.toLowerCase().contains(query) ||
            w.description.toLowerCase().contains(query);
      }).toList();
    }

    // Apply Brand Filter
    if (_selectedBrands.isNotEmpty) {
      list = list.where((w) => _selectedBrands.contains(w.brand)).toList();
    }

    // Apply Category Filter
    if (_selectedCategories.isNotEmpty) {
      list = list.where((w) => _selectedCategories.contains(w.category)).toList();
    }

    // Apply Price Range Filter
    list = list.where((w) {
      final price = w.discountedPrice;
      return price >= _priceRange.start && price <= _priceRange.end;
    }).toList();

    // Apply Rating Filter
    if (_minRating > 0.0) {
      list = list.where((w) => w.rating >= _minRating).toList();
    }

    // Apply Sorting
    switch (_sortBy) {
      case 'Newest':
        list.sort((a, b) => b.isNewArrival ? 1 : -1);
        break;
      case 'Price Low → High':
        list.sort((a, b) => a.discountedPrice.compareTo(b.discountedPrice));
        break;
      case 'Price High → Low':
        list.sort((a, b) => b.discountedPrice.compareTo(a.discountedPrice));
        break;
      case 'Popularity':
      default:
        list.sort((a, b) => b.isPopular ? 1 : -1);
        break;
    }

    return list;
  }

  // Add to recently viewed list (max 8, no duplicates, move to first if viewed again)
  void addToRecentlyViewed(Watch watch) {
    _recentlyViewed.removeWhere((w) => w.id == watch.id);
    _recentlyViewed.insert(0, watch);
    if (_recentlyViewed.length > 8) {
      _recentlyViewed.removeLast();
    }
    notifyListeners();
  }

  // Simulate pull-to-refresh
  Future<void> refreshProducts() async {
    _isLoading = true;
    notifyListeners();

    // Simulate network delay
    await Future.delayed(const Duration(milliseconds: 1000));

    _isLoading = false;
    notifyListeners();
  }
}
