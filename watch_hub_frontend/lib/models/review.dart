class Review {
  final String id;
  final String userName;
  final double rating;
  final String comment;
  final DateTime date;

  Review({
    required this.id,
    required this.userName,
    required this.rating,
    required this.comment,
    required this.date,
  });

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

  // =============================================
  // FROM JSON
  // =============================================

  // factory Review.fromJson(Map<String, dynamic> json) {
  //   return Review(
  //     id: json['id']?.toString() ?? '',
  //     userName:
  //         json['user']?['name'] ?? json['user_name'] ?? json['name'] ?? '',
  //     rating: _toDouble(json['rating']),
  //     comment: json['comment'] ?? json['review'] ?? '',
  //     date: json['created_at'] != null
  //         ? DateTime.parse(json['created_at'])
  //         : json['date'] != null
  //         ? DateTime.parse(json['date'])
  //         : DateTime.now(),
  //   );
  // }

  factory Review.fromJson(Map<String, dynamic> json) {
    print('🟡 [REVIEW] Parsing: $json');

    return Review(
      id: json['id']?.toString() ?? '',
      userName: json['user']?['name'] ?? json['user_name'] ?? 'Unknown',
      rating: (json['rating'] ?? 0).toDouble(),
      comment: json['comment'] ?? '',
      date: json['created_at'] != null
          ? DateTime.parse(json['created_at'])
          : DateTime.now(),
    );
  }

  // =============================================
  // TO JSON
  // =============================================

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'user_name': userName,
      'rating': rating,
      'comment': comment,
      'created_at': date.toIso8601String(),
    };
  }
}
