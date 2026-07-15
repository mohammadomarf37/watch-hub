import 'package:watch_hub_frontend/models/review.dart';

class Watch {
  final String id;
  final String brand;
  final String model;
  final String category;
  final double price;
  final double discount; // percentage (e.g., 0.15 for 15%)
  final String description;
  final Map<String, String> specifications; // Movement, Case, Glass, Water Resistance, etc.
  final List<String> images;
  final double rating;
  final List<Review> reviews;
  final int stock;
  final List<String> colors; // Color names or hex strings (e.g. ["Gold", "Silver"])
  final List<String> sizes; // Sizes (e.g., ["38mm", "40mm", "42mm"])
  final bool isFeatured;
  final bool isPopular;
  final bool isNewArrival;
  final bool isRecommended;

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
    required this.isPopular,
    required this.isNewArrival,
    required this.isRecommended,
  });

  double get discountedPrice => price * (1.0 - discount);
}
