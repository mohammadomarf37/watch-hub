import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final user = authProvider.currentUser;

    final name = user?.name ?? 'Guest User';
    final email = user?.email ?? 'guest@watchhub.com';
    final phone = user?.phone ?? 'Not Available';
    final avatar = user?.avatarUrl ?? '';

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('My Profile'),
        automaticallyImplyLeading: false,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(AppConstants.paddingLarge),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // 1. User Header Details (Avatar & Name/Email)
              Center(
                child: Column(
                  children: [
                    Container(
                      width: 100.0,
                      height: 100.0,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.primary, width: 3.0),
                        boxShadow: const [
                          BoxShadow(
                            color: AppColors.shadow,
                            blurRadius: 10.0,
                            offset: Offset(0.0, 5.0),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(50.0),
                        child: WatchImage(
                          imagePath: avatar,
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                    AppConstants.spacingMedium,
                    Text(
                      name,
                      style: theme.textTheme.headlineSmall?.copyWith(
                        color: AppColors.primary,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4.0),
                    Text(
                      email,
                      style: theme.textTheme.bodyMedium,
                    ),
                    const SizedBox(height: 2.0),
                    Text(
                      phone,
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: AppColors.textLight,
                      ),
                    ),
                  ],
                ),
              ),
              const Divider(height: 40.0),

              // 2. Menu Item Grid/List
              _buildMenuTile(
                icon: Icons.person_outline,
                title: 'Edit Profile',
                subtitle: 'Update your name, email, and phone',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.editProfile);
                },
              ),
              _buildMenuTile(
                icon: Icons.receipt_long_outlined,
                title: 'My Orders',
                subtitle: 'Track your deliveries and purchase history',
                onTap: () {
                  // Push main layout tab index 3 (Orders screen)
                  Navigator.pushNamedAndRemoveUntil(
                    context,
                    AppRoutes.mainLayout,
                    (route) => false,
                    arguments: 3,
                  );
                },
              ),
              _buildMenuTile(
                icon: Icons.location_on_outlined,
                title: 'Addresses',
                subtitle: 'Manage your primary shipping details',
                onTap: () {
                  ScaffoldMessenger.of(context).showSnackBar(
                    const SnackBar(
                      content: Text('Address Management is under development.'),
                    ),
                  );
                },
              ),
              _buildMenuTile(
                icon: Icons.notifications_none_outlined,
                title: 'Notifications',
                subtitle: 'View recent activities and discount alerts',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.notifications);
                },
              ),
              _buildMenuTile(
                icon: Icons.help_outline_outlined,
                title: 'Frequently Asked Questions',
                subtitle: 'Learn about return policies and warranties',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.faq);
                },
              ),
              _buildMenuTile(
                icon: Icons.support_agent_outlined,
                title: 'Customer Help Center',
                subtitle: 'Submit support tickets directly to our staff',
                onTap: () {
                  Navigator.pushNamed(context, AppRoutes.customerSupport);
                },
              ),
              AppConstants.spacingLarge,

              // 3. Logout Button
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: () {
                    authProvider.logout();
                    Navigator.pushNamedAndRemoveUntil(
                      context,
                      AppRoutes.login,
                      (route) => false,
                    );
                  },
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: AppColors.error, width: 1.5),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                    ),
                    padding: const EdgeInsets.symmetric(vertical: 14.0),
                  ),
                  child: const Text(
                    'LOGOUT',
                    style: TextStyle(
                      color: AppColors.error,
                      fontWeight: FontWeight.bold,
                      fontSize: 16.0,
                    ),
                  ),
                ),
              ),
              AppConstants.spacingLarge,
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMenuTile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12.0),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
        border: Border.all(color: AppColors.border),
      ),
      child: ListTile(
        leading: Container(
          padding: const EdgeInsets.all(8.0),
          decoration: const BoxDecoration(
            color: Colors.white,
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: AppColors.primary, size: 22.0),
        ),
        title: Text(
          title,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            color: AppColors.textPrimary,
            fontSize: 15.0,
          ),
        ),
        subtitle: Text(
          subtitle,
          style: const TextStyle(
            color: AppColors.textSecondary,
            fontSize: 12.0,
          ),
        ),
        trailing: const Icon(Icons.chevron_right, color: AppColors.textLight),
        onTap: onTap,
      ),
    );
  }
}

// Extension to allow quick outlined button bindings
extension OutlinedButtonExtension on BorderSide {
  Widget textButton({required VoidCallback onPressed, required Widget child}) {
    return OutlinedButton(
      onPressed: onPressed,
      style: OutlinedButton.styleFrom(
        side: this,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
        ),
        padding: const EdgeInsets.symmetric(vertical: 14.0),
      ),
      child: child,
    );
  }
}
