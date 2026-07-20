import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;
  late Animation<double> _opacityAnimation;
  bool _isNavigating = false; // ✅ Prevent duplicate navigation

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    );

    _scaleAnimation = Tween<double>(
      begin: 0.8,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeOutBack));

    _opacityAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeIn));

    _controller.forward();

    _navigateToNext();
  }

  Future<void> _navigateToNext() async {
    await Future.delayed(const Duration(milliseconds: 2500));

    if (!mounted || _isNavigating) return; // ✅ Prevent duplicate

    _isNavigating = true;

    try {
      final storage = StorageService();

      // ✅ Check onboarding status
      final hasSeenOnboarding = await storage.hasSeenOnboarding();
      print('🟡 [SPLASH] Onboarding seen: $hasSeenOnboarding');

      // ✅ Check auth status
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      final token = await storage.getToken();
      final isLoggedIn = token != null && token.isNotEmpty;

      print('🟡 [SPLASH] Is Logged In: $isLoggedIn');
      print('🟡 [SPLASH] Is Guest: ${authProvider.isGuest}');

      String route;

      if (!hasSeenOnboarding) {
        // ✅ First time user - show onboarding
        route = AppRoutes.onboarding;
      } else if (isLoggedIn || authProvider.isLoggedIn) {
        // ✅ User is logged in - go to main
        route = AppRoutes.mainLayout;
      } else {
        // ✅ Not logged in - show login
        route = AppRoutes.login;
      }

      print('🟡 [SPLASH] Navigating to: $route');

      if (mounted) {
        // ✅ Use pushReplacementNamed to remove splash from stack
        Navigator.pushReplacementNamed(context, route);
      }
    } catch (e) {
      print('🔴 [SPLASH] Error: $e');
      if (mounted) {
        Navigator.pushReplacementNamed(context, AppRoutes.login);
      }
    }
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: Center(
        child: AnimatedBuilder(
          animation: _controller,
          builder: (context, child) {
            return Opacity(
              opacity: _opacityAnimation.value,
              child: Transform.scale(
                scale: _scaleAnimation.value,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Logo
                    Container(
                      width: 100.0,
                      height: 100.0,
                      decoration: BoxDecoration(
                        color: Colors.white,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: AppColors.primary,
                          width: 4.0,
                        ),
                        boxShadow: const [
                          BoxShadow(
                            color: AppColors.shadow,
                            blurRadius: 20.0,
                            offset: Offset(0.0, 10.0),
                          ),
                        ],
                      ),
                      alignment: Alignment.center,
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          const Icon(
                            Icons.watch_outlined,
                            color: AppColors.primary,
                            size: 56.0,
                          ),
                          Positioned(
                            top: 38.0,
                            left: 45.0,
                            child: Container(
                              width: 15.0,
                              height: 2.0,
                              color: AppColors.secondary,
                            ),
                          ),
                          Positioned(
                            top: 25.0,
                            left: 45.0,
                            child: Container(
                              width: 2.0,
                              height: 15.0,
                              color: AppColors.secondary,
                            ),
                          ),
                        ],
                      ),
                    ),
                    AppConstants.spacingLarge,
                    Text(
                      AppConstants.appName.toUpperCase(),
                      style: theme.textTheme.headlineMedium?.copyWith(
                        color: AppColors.primary,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 3.0,
                      ),
                    ),
                    const SizedBox(height: 6.0),
                    Text(
                      'TIMELESS ELEGANCE',
                      style: theme.textTheme.labelMedium?.copyWith(
                        color: AppColors.secondary,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }
}
