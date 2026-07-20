import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_theme.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/product_provider.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/wishlist_provider.dart';
import 'package:watch_hub_frontend/providers/order_provider.dart';
import 'package:watch_hub_frontend/providers/profile_provider.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final storage = StorageService();
  final hasSeenOnboarding = await storage.hasSeenOnboarding();

  final authProvider = AuthProvider();
  await authProvider.loadSession();

  // ✅ Determine initial route - ALWAYS go to splash first
  String initialRoute = AppRoutes.splash;

  print('🟡 [MAIN] Onboarding seen: $hasSeenOnboarding');
  print('🟡 [MAIN] Is Logged In: ${authProvider.isLoggedIn}');
  print('🟡 [MAIN] Is Guest: ${authProvider.isGuest}');
  print('🟡 [MAIN] Initial Route: $initialRoute');

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider<AuthProvider>.value(value: authProvider),
        ChangeNotifierProvider(create: (_) => ProductProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => WishlistProvider()),
        ChangeNotifierProvider(create: (_) => OrderProvider()),
        ChangeNotifierProvider(create: (_) => ProfileProvider()),
      ],
      child: MyApp(initialRoute: initialRoute),
    ),
  );
}

class MyApp extends StatelessWidget {
  final String initialRoute;

  const MyApp({super.key, required this.initialRoute});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'WatchHub',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      initialRoute: initialRoute,
      onGenerateRoute: AppRoutes.generateRoute,
    );
  }
}
