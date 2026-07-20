class UserProfile {
  final int? id;
  final String name;
  final String email;
  final String phone;
  final String avatarUrl;
  final String address;

  UserProfile({
    this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.avatarUrl,
    required this.address,
  });

  /// Builds a [UserProfile] from the Laravel `User` JSON object
  /// (as returned by /auth/login, /auth/register, /auth/user).
  ///
  /// Note: the backend doesn't return a shipping address on the user object
  /// itself (addresses live in a separate `/addresses` endpoint), so it
  /// defaults to a placeholder until that screen is wired up.
  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: json['id'] is int ? json['id'] as int : int.tryParse('${json['id']}'),
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      avatarUrl: json['profile_image']?.toString().isNotEmpty == true
          ? json['profile_image'].toString()
          : 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150',
      address: 'Add your shipping address in Profile settings.',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'phone': phone,
      'profile_image': avatarUrl,
    };
  }

  UserProfile copyWith({
    int? id,
    String? name,
    String? email,
    String? phone,
    String? avatarUrl,
    String? address,
  }) {
    return UserProfile(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      phone: phone ?? this.phone,
      avatarUrl: avatarUrl ?? this.avatarUrl,
      address: address ?? this.address,
    );
  }
}
