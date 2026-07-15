import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';

class NotificationModel {
  final String title;
  final String body;
  final IconData icon;
  final Color iconColor;
  final String time;
  final bool isRead;

  NotificationModel({
    required this.title,
    required this.body,
    required this.icon,
    required this.iconColor,
    required this.time,
    this.isRead = false,
  });
}

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    // Mock Notifications Dataset
    final List<NotificationModel> notifications = [
      NotificationModel(
        title: 'Order Dispatched!',
        body: 'Your order #WH-82736 has been shipped via express courier and is on its way.',
        icon: Icons.local_shipping_outlined,
        iconColor: AppColors.success,
        time: '2 hours ago',
      ),
      NotificationModel(
        title: 'Special Summer Gold Sale',
        body: 'Get up to 15% off on all Gold dial luxury watches. Limited stock available.',
        icon: Icons.discount_outlined,
        iconColor: AppColors.secondary,
        time: '5 hours ago',
        isRead: true,
      ),
      NotificationModel(
        title: 'Support Ticket Solved',
        body: 'Response from support: "Your address change request has been updated. Secure transit is assured."',
        icon: Icons.support_agent_outlined,
        iconColor: AppColors.primary,
        time: '1 day ago',
        isRead: true,
      ),
      NotificationModel(
        title: 'Welcome to WatchHub!',
        body: 'Discover our premium collections and personalize your profile details.',
        icon: Icons.watch_outlined,
        iconColor: AppColors.primary,
        time: '2 days ago',
        isRead: true,
      ),
    ];

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Notifications'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: ListView.builder(
          padding: const EdgeInsets.all(AppConstants.paddingMedium),
          itemCount: notifications.length,
          itemBuilder: (context, index) {
            final item = notifications[index];

            return Container(
              margin: const EdgeInsets.only(bottom: AppConstants.paddingSmall),
              padding: const EdgeInsets.all(AppConstants.paddingMedium),
              decoration: BoxDecoration(
                color: item.isRead ? Colors.white : AppColors.surface,
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
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
                  // Icon node
                  CircleAvatar(
                    backgroundColor: item.iconColor.withOpacity(0.1),
                    radius: 20.0,
                    child: Icon(item.icon, color: item.iconColor, size: 20.0),
                  ),
                  AppConstants.spacingMedium,
                  // Text details
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
                                  decoration: item.isRead ? null : TextDecoration.underline,
                                ),
                              ),
                            ),
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
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
