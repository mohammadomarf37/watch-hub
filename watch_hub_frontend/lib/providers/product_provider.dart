import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:watch_hub_frontend/core/network/api_config.dart';
import 'package:watch_hub_frontend/models/watch.dart';
import 'package:watch_hub_frontend/models/review.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

class ProductProvider extends ChangeNotifier {
  final Dio _dio = Dio();
  final StorageService _storage = StorageService();

  List<Watch> _allWatches = [];
  List<Watch> _recentlyViewed = [];
  bool _isLoading = false;
  String? _errorMessage;

  // Filter States
  String _searchQuery = '';
  List<String> _selectedBrands = [];
  List<String> _selectedCategories = [];
  RangeValues _priceRange = const RangeValues(0.0, 15000.0);
  double _minRating = 0.0;
  String _sortBy = 'Popularity';

  // =============================================
  // GETTERS
  // =============================================

  List<Watch> get allWatches => _allWatches;
  List<Watch> get recentlyViewed => _recentlyViewed;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String get searchQuery => _searchQuery;
  List<String> get selectedBrands => _selectedBrands;
  List<String> get selectedCategories => _selectedCategories;
  RangeValues get priceRange => _priceRange;
  double get minRating => _minRating;
  String get sortBy => _sortBy;

  List<String> get brands => _allWatches.map((w) => w.brand).toSet().toList();
  List<String> get categories =>
      _allWatches.map((w) => w.category).toSet().toList();

  // =============================================
  // FILTER SETTERS
  // =============================================

  void resetFilters() {
    _searchQuery = '';
    _selectedBrands = [];
    _selectedCategories = [];
    _priceRange = const RangeValues(0.0, 15000.0);
    _minRating = 0.0;
    _sortBy = 'Popularity';
    notifyListeners();
  }

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

  // =============================================
  // FILTERED & SORTED WATCHES
  // =============================================

  List<Watch> get filteredWatches {
    List<Watch> list = List.from(_allWatches);

    if (_searchQuery.isNotEmpty) {
      final query = _searchQuery.toLowerCase();
      list = list.where((w) {
        return w.brand.toLowerCase().contains(query) ||
            w.model.toLowerCase().contains(query) ||
            w.category.toLowerCase().contains(query) ||
            w.description.toLowerCase().contains(query);
      }).toList();
    }

    if (_selectedBrands.isNotEmpty) {
      list = list.where((w) => _selectedBrands.contains(w.brand)).toList();
    }

    if (_selectedCategories.isNotEmpty) {
      list = list
          .where((w) => _selectedCategories.contains(w.category))
          .toList();
    }

    list = list.where((w) {
      final price = w.discountedPrice;
      return price >= _priceRange.start && price <= _priceRange.end;
    }).toList();

    if (_minRating > 0.0) {
      list = list.where((w) => w.rating >= _minRating).toList();
    }

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
        list.sort((a, b) => (b.reviews.length).compareTo(a.reviews.length));
        break;
    }

    return list;
  }

  // =============================================
  // HEADERS
  // =============================================

  Map<String, String> _getHeaders() {
    final token = _storage.getToken();
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  // =============================================
  // API CALLS
  // =============================================

  // Fetch all watches
  // Future<void> fetchWatches() async {
  //   _isLoading = true;
  //   _errorMessage = null;
  //   notifyListeners();

  //   try {
  //     final response = await _dio.get(
  //       ApiConfig.watches,
  //       options: Options(headers: _getHeaders()),
  //     );

  //     if (response.statusCode == 200) {
  //       final data = response.data;
  //       final watchesData = data['data']['data'] as List? ?? data['data'] as List? ?? [];
  //       _allWatches = watchesData.map((e) => Watch.fromJson(e)).toList();
  //       _errorMessage = null;
  //     } else {
  //       _errorMessage = 'Failed to load products';
  //     }
  //   } catch (e) {
  //     if (e is DioException) {
  //       if (e.type == DioExceptionType.connectionTimeout ||
  //           e.type == DioExceptionType.receiveTimeout) {
  //         _errorMessage = 'Connection timeout. Please check your internet.';
  //       } else if (e.response?.statusCode == 401) {
  //         _errorMessage = 'Session expired. Please login again.';
  //       } else {
  //         _errorMessage = 'Something went wrong: ${e.message}';
  //       }
  //     } else {
  //       _errorMessage = e.toString();
  //     }
  //   } finally {
  //     _isLoading = false;
  //     notifyListeners();
  //   }
  // }

  // ✅ FAST - Sirf watches fetch karo (No reviews on home)
  Future<void> fetchWatches() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final response = await _dio.get(
        ApiConfig.watches,
        options: Options(headers: _getHeaders()),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final watchesData =
            data['data']['data'] as List? ?? data['data'] as List? ?? [];

        print('🟡 [PROVIDER] Total watches from API: ${watchesData.length}');

        // ✅ Sirf watches parse karo, reviews nahi (fast)
        _allWatches = watchesData.map((e) => Watch.fromJson(e)).toList();

        print('🟡 [PROVIDER] Done fetching watches!');
        _errorMessage = null;
      } else {
        _errorMessage = 'Failed to load products';
      }
    } catch (e) {
      if (e is DioException) {
        if (e.type == DioExceptionType.connectionTimeout ||
            e.type == DioExceptionType.receiveTimeout) {
          _errorMessage = 'Connection timeout. Please check your internet.';
        } else if (e.response?.statusCode == 401) {
          _errorMessage = 'Session expired. Please login again.';
        } else {
          _errorMessage = 'Something went wrong: ${e.message}';
        }
      } else {
        _errorMessage = e.toString();
      }
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ✅ Fetch single watch with reviews
  Future<Watch?> fetchWatchWithReviews(String id) async {
    try {
      final response = await _dio.get(
        '${ApiConfig.watchDetail}/$id',
        options: Options(headers: _getHeaders()),
      );

      if (response.statusCode != 200) return null;

      final data = response.data;
      Watch watch = Watch.fromJson(data['data']);

      // Fetch reviews for this watch
      try {
        final reviewsResponse = await _dio.get(
          ApiConfig.watchReviews(id),
          options: Options(headers: _getHeaders()),
        );

        if (reviewsResponse.statusCode == 200) {
          final reviewsData = reviewsResponse.data['data']['reviews'] ?? [];
          watch = Watch(
            id: watch.id,
            brand: watch.brand,
            model: watch.model,
            category: watch.category,
            price: watch.price,
            discount: watch.discount,
            description: watch.description,
            specifications: watch.specifications,
            images: watch.images,
            rating: watch.rating,
            reviews: reviewsData.map((e) => Review.fromJson(e)).toList(),
            stock: watch.stock,
            colors: watch.colors,
            sizes: watch.sizes,
            isFeatured: watch.isFeatured,
            createdAt: watch.createdAt,
          );
        }
      } catch (e) {
        // Reviews fetch failed, return watch without reviews
      }

      return watch;
    } catch (e) {
      return null;
    }
  }

  // Fetch featured watches
  Future<List<Watch>> fetchFeaturedWatches() async {
    try {
      final response = await _dio.get(
        ApiConfig.featuredWatches,
        options: Options(headers: _getHeaders()),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final watchesData = data['data'] as List? ?? [];
        return watchesData.map((e) => Watch.fromJson(e)).toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  // Fetch new arrivals
  Future<List<Watch>> fetchNewArrivals() async {
    try {
      final response = await _dio.get(
        ApiConfig.newArrivals,
        options: Options(headers: _getHeaders()),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final watchesData = data['data'] as List? ?? [];
        return watchesData.map((e) => Watch.fromJson(e)).toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  // Fetch recommended watches
  Future<List<Watch>> fetchRecommendedWatches() async {
    try {
      final response = await _dio.get(
        ApiConfig.recommendedWatches,
        options: Options(headers: _getHeaders()),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final watchesData = data['data'] as List? ?? [];
        return watchesData.map((e) => Watch.fromJson(e)).toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  // Refresh products (pull to refresh)
  Future<void> refreshProducts() async {
    await fetchWatches();
  }

  // =============================================
  // RECENTLY VIEWED
  // =============================================

  void addToRecentlyViewed(Watch watch) {
    _recentlyViewed.removeWhere((w) => w.id == watch.id);
    _recentlyViewed.insert(0, watch);
    if (_recentlyViewed.length > 8) {
      _recentlyViewed.removeLast();
    }
    notifyListeners();
  }

  // =============================================
  // LOAD DUMMY DATA (Fallback)
  // =============================================

  void loadDummyData(List<Watch> watches) {
    _allWatches = watches;
    notifyListeners();
  }

  // =============================================
  // DISPOSE
  // =============================================

  @override
  void dispose() {
    _dio.close();
    super.dispose();
  }
}
