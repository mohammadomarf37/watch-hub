import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';

class NotificationModel {
  final String id;
  final String title;
  final String body;
  final IconData icon;
  final Color iconColor;
  final String time;
  final bool isRead;
  final String? type;

  NotificationModel({
    required this.id,
    required this.title,
    required this.body,
    required this.icon,
    required this.iconColor,
    required this.time,
    this.isRead = false,
    this.type,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    final type = json['type'] ?? 'general';

    final iconMap = {
      'order': Icons.local_shipping_outlined,
      'promotion': Icons.discount_outlined,
      'wishlist': Icons.favorite_outlined,
      'support': Icons.support_agent_outlined,
      'general': Icons.notifications_outlined,
    };

    final colorMap = {
      'order': AppColors.success,
      'promotion': AppColors.secondary,
      'wishlist': AppColors.favoriteRed,
      'support': AppColors.primary,
      'general': AppColors.primary,
    };

    return NotificationModel(
      id: json['id']?.toString() ?? '',
      title: json['title'] ?? 'Notification',
      body: json['message'] ?? json['body'] ?? '',
      icon: iconMap[type] ?? Icons.notifications_outlined,
      iconColor: colorMap[type] ?? AppColors.primary,
      time: _formatTime(json['created_at']),
      isRead: json['read_at'] != null,
      type: type,
    );
  }

  // ✅ FIXED: _formatTime method - handles both String and null
  static String _formatTime(dynamic dateTimeValue) {
    if (dateTimeValue == null) {
      return 'Just now';
    }

    try {
      DateTime dateTime;
      if (dateTimeValue is DateTime) {
        dateTime = dateTimeValue;
      } else if (dateTimeValue is String) {
        dateTime = DateTime.parse(dateTimeValue);
      } else {
        return 'Just now';
      }
      return _timeAgo(dateTime);
    } catch (e) {
      return 'Just now';
    }
  }

  static String _timeAgo(DateTime dateTime) {
    final difference = DateTime.now().difference(dateTime);

    if (difference.inDays > 7) {
      return '${difference.inDays ~/ 7}w ago';
    } else if (difference.inDays > 0) {
      return '${difference.inDays}d ago';
    } else if (difference.inHours > 0) {
      return '${difference.inHours}h ago';
    } else if (difference.inMinutes > 0) {
      return '${difference.inMinutes}m ago';
    } else {
      return 'Just now';
    }
  }

  NotificationModel copyWith({
    String? id,
    String? title,
    String? body,
    IconData? icon,
    Color? iconColor,
    String? time,
    bool? isRead,
    String? type,
  }) {
    return NotificationModel(
      id: id ?? this.id,
      title: title ?? this.title,
      body: body ?? this.body,
      icon: icon ?? this.icon,
      iconColor: iconColor ?? this.iconColor,
      time: time ?? this.time,
      isRead: isRead ?? this.isRead,
      type: type ?? this.type,
    );
  }
}
