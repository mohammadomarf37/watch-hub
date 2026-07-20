import 'package:watch_hub_frontend/models/review.dart';

class Watch {
  final String id;
  final String brand;
  final String model;
  final String category;
  final double price;
  final double discount;
  final String description;
  final Map<String, String> specifications;
  final List<String> images;
  final double rating;
  final int? ratingCount; // ✅ ADD THIS
  final List<Review> reviews;
  final int stock;
  final List<String> colors;
  final List<String> sizes;
  final bool isFeatured;
  final DateTime? createdAt;

  Watch({
    required this.id,
    required this.brand,
    required this.model,
    required this.category,
    required this.price,
    required this.discount,
    required this.description,
    required this.specifications,
    required this.images,
    required this.rating,
    required this.reviews,
    required this.stock,
    required this.colors,
    required this.sizes,
    required this.isFeatured,
    this.ratingCount, // ✅ ADD THIS
    this.createdAt,
  });

  double get discountedPrice => price * (1.0 - discount);

  // Popular - Reviews count OR Rating based
  bool get isPopular {
    // Agar reviews hain toh unke count ke hisaab se
    if (reviews.isNotEmpty) {
      return reviews.length > 2; // 2 se zyada reviews = popular
    }
    // Agar reviews nahi hain toh rating ke hisaab se
    return rating >= 4.0; // Rating >= 4 wale popular
  }

  // ✅ FIXED - New Arrival: created within 30 days
  bool get isNewArrival {
    if (createdAt == null) return false;
    final days = DateTime.now().difference(createdAt!).inDays;
    return days <= 30;
  }

  // ✅ FIXED - Recommended: rating >= 4.0
  bool get isRecommended => rating >= 4.0;

  bool get isOnSale => discount > 0;
  bool get isInStock => stock > 0;
  double get originalPrice => price;

  // =============================================
  // HELPER - Safe Double Conversion
  // =============================================

  static double _toDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is double) return value;
    if (value is int) return value.toDouble();
    if (value is String) {
      final cleaned = value.replaceAll(RegExp(r'[^0-9.]'), '');
      return double.tryParse(cleaned) ?? 0.0;
    }
    return 0.0;
  }

  static int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) {
      final cleaned = value.replaceAll(RegExp(r'[^0-9.]'), '');
      return int.tryParse(cleaned) ?? 0;
    }
    return 0;
  }

  // =============================================
  // FROM JSON
  // =============================================

  factory Watch.fromJson(Map<String, dynamic> json) {
    return Watch(
      id: json['id']?.toString() ?? '',
      brand: json['brand']?['name'] ?? json['brand_name'] ?? '',
      model: json['model'] ?? '',
      category: json['category']?['name'] ?? json['category_name'] ?? '',
      price: _toDouble(json['base_price']),
      discount: _toDouble(json['discount_percent']) / 100,
      description: json['description'] ?? '',
      specifications: json['specifications'] != null
          ? Map<String, String>.from(json['specifications'])
          : {},
      images: json['images'] != null
          ? (json['images'] as List)
                .map((e) => e['image_url']?.toString() ?? '')
                .toList()
          : [],
      rating: _toDouble(json['rating']),
      ratingCount: json['rating_count'] as int?, // ✅ ADD THIS
      reviews: json['reviews'] != null
          ? (json['reviews'] as List).map((e) => Review.fromJson(e)).toList()
          : [],
      stock: _toInt(json['stock']),
      colors: json['colors'] != null
          ? (json['colors'] as List).map((e) => e.toString()).toList()
          : [],
      sizes: json['sizes'] != null
          ? (json['sizes'] as List).map((e) => e.toString()).toList()
          : [],
      isFeatured: json['is_featured'] ?? false,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : null,
    );
  }

  // =============================================
  // TO JSON
  // =============================================

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'brand': brand,
      'model': model,
      'category': category,
      'price': price,
      'discount': discount,
      'description': description,
      'specifications': specifications,
      'images': images,
      'rating': rating,
      'reviews': reviews.map((e) => e.toJson()).toList(),
      'stock': stock,
      'colors': colors,
      'sizes': sizes,
      'isFeatured': isFeatured,
      'isPopular': isPopular,
      'isNewArrival': isNewArrival,
      'isRecommended': isRecommended,
    };
  }
}
