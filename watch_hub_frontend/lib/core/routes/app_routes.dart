import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/models/watch.dart';
import 'package:watch_hub_frontend/models/order.dart';
import 'package:watch_hub_frontend/screens/splash_screen.dart';
import 'package:watch_hub_frontend/screens/onboarding_screen.dart';
import 'package:watch_hub_frontend/screens/login_screen.dart';
import 'package:watch_hub_frontend/screens/signup_screen.dart';
import 'package:watch_hub_frontend/screens/main_layout.dart';
import 'package:watch_hub_frontend/screens/product_details_screen.dart';
import 'package:watch_hub_frontend/screens/checkout_screen.dart';
import 'package:watch_hub_frontend/screens/order_details_screen.dart';
import 'package:watch_hub_frontend/screens/edit_profile_screen.dart';
import 'package:watch_hub_frontend/screens/notifications_screen.dart';
import 'package:watch_hub_frontend/screens/faq_screen.dart';
import 'package:watch_hub_frontend/screens/customer_support_screen.dart';

class AppRoutes {
  AppRoutes._();

  static const String splash = '/';
  static const String onboarding = '/onboarding';
  static const String login = '/login';
  static const String signup = '/signup';
  static const String mainLayout = '/main';
  static const String productDetails = '/product-details';
  static const String checkout = '/checkout';
  static const String orderDetails = '/order-details';
  static const String editProfile = '/edit-profile';
  static const String notifications = '/notifications';
  static const String faq = '/faq';
  static const String customerSupport = '/customer-support';

  static Route<dynamic> generateRoute(RouteSettings settings) {
    switch (settings.name) {
      case splash:
        return _fadeRoute(const SplashScreen(), settings);
      case onboarding:
        return _slideRoute(const OnboardingScreen(), settings);
      case login:
        return _fadeRoute(const LoginScreen(), settings);
      case signup:
        return _fadeRoute(const SignupScreen(), settings);
      case mainLayout:
        final initialIndex = settings.arguments as int? ?? 0;
        return _fadeRoute(MainLayout(initialIndex: initialIndex), settings);
      case productDetails:
        final watch = settings.arguments as Watch;
        return _slideRoute(ProductDetailsScreen(watch: watch), settings);
      case checkout:
        return _slideRoute(const CheckoutScreen(), settings);
      case orderDetails:
        final order = settings.arguments as OrderModel;
        return _slideRoute(OrderDetailsScreen(order: order), settings);
      case editProfile:
        return _slideRoute(const EditProfileScreen(), settings);
      case notifications:
        return _slideRoute(const NotificationsScreen(), settings);
      case faq:
        return _slideRoute(const FAQScreen(), settings);
      case customerSupport:
        return _slideRoute(const CustomerSupportScreen(), settings);
      default:
        return MaterialPageRoute(
          builder: (_) => Scaffold(
            body: Center(
              child: Text('No route defined for ${settings.name}'),
            ),
          ),
        );
    }
  }

  static PageRouteBuilder _fadeRoute(Widget child, RouteSettings settings) {
    return PageRouteBuilder(
      settings: settings,
      pageBuilder: (context, animation, secondaryAnimation) => child,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        return FadeTransition(
          opacity: animation,
          child: child,
        );
      },
      transitionDuration: const Duration(milliseconds: 300),
    );
  }

  static PageRouteBuilder _slideRoute(Widget child, RouteSettings settings) {
    return PageRouteBuilder(
      settings: settings,
      pageBuilder: (context, animation, secondaryAnimation) => child,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        const begin = Offset(1.0, 0.0);
        const end = Offset.zero;
        const curve = Curves.easeInOut;

        var tween = Tween(begin: begin, end: end).chain(CurveTween(curve: curve));

        return SlideTransition(
          position: animation.drive(tween),
          child: child,
        );
      },
      transitionDuration: const Duration(milliseconds: 350),
    );
  }
}
