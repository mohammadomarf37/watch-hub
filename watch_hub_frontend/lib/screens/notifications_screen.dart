import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/network/api_config.dart';
import 'package:watch_hub_frontend/core/widgets/empty_state.dart';
import 'package:watch_hub_frontend/models/notification.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<NotificationModel> _notifications = [];
  bool _isLoading = true;
  bool _isGuest = false;

  final Dio _dio = Dio();
  final StorageService _storage = StorageService();

  @override
  void initState() {
    super.initState();
    _checkAuthAndFetch();
  }

  Future<void> _checkAuthAndFetch() async {
    // ✅ Check if user is logged in directly
    final isLoggedIn = await _storage.isLoggedIn();

    if (!isLoggedIn) {
      setState(() {
        _isGuest = true;
        _isLoading = false;
      });
      return;
    }

    // ✅ User is logged in, fetch notifications
    setState(() {
      _isGuest = false;
    });
    await _fetchNotifications();
  }

  Future<void> _fetchNotifications() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final token = await _storage.getToken();

      if (token == null || token.isEmpty) {
        setState(() {
          _isGuest = true;
          _isLoading = false;
        });
        return;
      }

      final response = await _dio.get(
        ApiConfig.notifications,
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        ),
      );

      if (response.statusCode == 200) {
        final data = response.data;
        final notificationsData = data['data']['notifications']['data'] ?? [];

        final notifications = notificationsData
            .map<NotificationModel>((e) => NotificationModel.fromJson(e))
            .toList();

        setState(() {
          _notifications = notifications;
          _isLoading = false;
        });
      } else {
        setState(() {
          _isLoading = false;
        });
      }
    } on DioException catch (e) {
      print('🔴 [NOTIFICATIONS] Dio Error: ${e.message}');

      if (e.response?.statusCode == 401) {
        await _storage.logout();
        setState(() {
          _isGuest = true;
          _isLoading = false;
        });

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Session expired. Please login again.'),
              duration: Duration(seconds: 3),
            ),
          );
        }
      } else {
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      print('🔴 [NOTIFICATIONS] Error: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _markAsRead(String id) async {
    try {
      final token = await _storage.getToken();
      if (token == null) return;

      await _dio.put(
        ApiConfig.notificationRead(id),
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        ),
      );

      setState(() {
        final index = _notifications.indexWhere((n) => n.id == id);
        if (index != -1) {
          _notifications[index] = _notifications[index].copyWith(isRead: true);
        }
      });
    } catch (e) {
      print('🔴 [NOTIFICATIONS] Mark as read error: $e');
    }
  }

  Future<void> _markAllAsRead() async {
    try {
      final token = await _storage.getToken();
      if (token == null) return;

      await _dio.put(
        ApiConfig.notificationReadAll,
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        ),
      );

      setState(() {
        _notifications = _notifications
            .map((n) => n.copyWith(isRead: true))
            .toList();
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('All notifications marked as read'),
          duration: Duration(seconds: 2),
        ),
      );
    } catch (e) {
      print('🔴 [NOTIFICATIONS] Mark all read error: $e');
    }
  }

  Future<void> _deleteNotification(String id) async {
    try {
      final token = await _storage.getToken();
      if (token == null) return;

      await _dio.delete(
        ApiConfig.notificationRead(id),
        options: Options(
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer $token',
          },
        ),
      );

      setState(() {
        _notifications.removeWhere((n) => n.id == id);
      });

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Notification deleted'),
          duration: Duration(seconds: 2),
        ),
      );
    } catch (e) {
      print('🔴 [NOTIFICATIONS] Delete error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Notifications'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          if (_notifications.isNotEmpty && !_isGuest)
            TextButton(
              onPressed: _markAllAsRead,
              child: const Text(
                'Mark All Read',
                style: TextStyle(
                  color: AppColors.primary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
        ],
      ),
      body: SafeArea(
        child: _isGuest
            ? _buildGuestView()
            : _isLoading
            ? _buildLoadingView()
            : _notifications.isEmpty
            ? _buildEmptyView()
            : _buildNotificationList(),
      ),
    );
  }

  Widget _buildLoadingView() {
    return const Center(
      child: CircularProgressIndicator(color: AppColors.primary),
    );
  }

  Widget _buildGuestView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.notifications_off_outlined,
            size: 80,
            color: AppColors.textLight,
          ),
          const SizedBox(height: 16),
          Text(
            'Login to see notifications',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Sign in to view your notifications and updates',
            style: TextStyle(color: AppColors.textSecondary),
          ),
          const SizedBox(height: 24),
          ElevatedButton(
            onPressed: () {
              Navigator.pushReplacementNamed(context, '/login');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              padding: const EdgeInsets.symmetric(horizontal: 40, vertical: 14),
            ),
            child: const Text('Login', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyView() {
    return const EmptyState(
      icon: Icons.notifications_off_outlined,
      title: 'No Notifications',
      description: 'You\'re all caught up! No new notifications.',
    );
  }

  Widget _buildNotificationList() {
    final theme = Theme.of(context);

    return RefreshIndicator(
      onRefresh: _fetchNotifications,
      color: AppColors.primary,
      child: ListView.builder(
        padding: const EdgeInsets.all(AppConstants.paddingMedium),
        itemCount: _notifications.length,
        itemBuilder: (context, index) {
          final item = _notifications[index];

          return Dismissible(
            key: Key(item.id),
            direction: DismissDirection.endToStart,
            background: Container(
              alignment: Alignment.centerRight,
              padding: const EdgeInsets.only(right: 20),
              decoration: BoxDecoration(
                color: Colors.red,
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              ),
              child: const Icon(Icons.delete_outline, color: Colors.white),
            ),
            onDismissed: (_) {
              _deleteNotification(item.id);
            },
            child: GestureDetector(
              onTap: () {
                if (!item.isRead) {
                  _markAsRead(item.id);
                }
              },
              child: Container(
                margin: const EdgeInsets.only(
                  bottom: AppConstants.paddingSmall,
                ),
                padding: const EdgeInsets.all(AppConstants.paddingMedium),
                decoration: BoxDecoration(
                  color: item.isRead ? Colors.white : AppColors.surface,
                  borderRadius: BorderRadius.circular(
                    AppConstants.radiusMedium,
                  ),
                  border: Border.all(color: AppColors.border),
                  boxShadow: const [
                    BoxShadow(
                      color: AppColors.shadow,
                      offset: Offset(0.0, 2.0),
                      blurRadius: 4.0,
                    ),
                  ],
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    CircleAvatar(
                      backgroundColor: item.iconColor.withOpacity(0.1),
                      radius: 20.0,
                      child: Icon(item.icon, color: item.iconColor, size: 20.0),
                    ),
                    AppConstants.spacingMedium,
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  item.title,
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: AppColors.textPrimary,
                                    fontSize: 14.0,
                                    decoration: item.isRead
                                        ? null
                                        : TextDecoration.underline,
                                  ),
                                ),
                              ),
                              if (!item.isRead)
                                Container(
                                  width: 8,
                                  height: 8,
                                  decoration: const BoxDecoration(
                                    color: AppColors.primary,
                                    shape: BoxShape.circle,
                                  ),
                                ),
                              const SizedBox(width: 8),
                              Text(
                                item.time,
                                style: const TextStyle(
                                  color: AppColors.textLight,
                                  fontSize: 11.0,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 4.0),
                          Text(
                            item.body,
                            style: theme.textTheme.bodyMedium?.copyWith(
                              fontSize: 13.0,
                              height: 1.4,
                            ),
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
