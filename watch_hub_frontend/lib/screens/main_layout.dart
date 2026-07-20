import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/widgets/guest_dialog.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/cart_provider.dart';
import 'package:watch_hub_frontend/providers/wishlist_provider.dart';
import 'package:watch_hub_frontend/screens/home_screen.dart';
import 'package:watch_hub_frontend/screens/wishlist_screen.dart';
import 'package:watch_hub_frontend/screens/cart_screen.dart';
import 'package:watch_hub_frontend/screens/orders_screen.dart';
import 'package:watch_hub_frontend/screens/profile_screen.dart';

class MainLayout extends StatefulWidget {
  final int initialIndex;

  const MainLayout({super.key, this.initialIndex = 0});

  @override
  State<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends State<MainLayout> {
  late int _currentIndex;

  final List<Widget> _screens = [
    const HomeScreen(),
    const WishlistScreen(),
    const CartScreen(),
    const OrdersScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
  }

  // ✅ Check if user is authenticated
  bool _isAuthenticated(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    return authProvider.isAuthenticated && authProvider.token != null;
  }

  @override
  Widget build(BuildContext context) {
    final cartProvider = Provider.of<CartProvider>(context);
    final wishlistProvider = Provider.of<WishlistProvider>(context);
    final authProvider = Provider.of<AuthProvider>(context);

    // ✅ Guest mode - only home is accessible
    final isGuest = authProvider.isGuest || authProvider.token == null;

    return Scaffold(
      body: IndexedStack(index: _currentIndex, children: _screens),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) {
          // ✅ Home is always accessible
          if (index == 0) {
            setState(() {
              _currentIndex = index;
            });
            return;
          }

          // ✅ Check if user is authenticated for other tabs
          if (isGuest) {
            showGuestDialog(context);
            return;
          }

          // ✅ Authenticated user can access all tabs
          setState(() {
            _currentIndex = index;
          });
        },
        backgroundColor: Colors.white,
        indicatorColor: AppColors.primary.withOpacity(0.08),
        elevation: 10.0,
        destinations: [
          // Home - Always accessible
          const NavigationDestination(
            icon: Icon(Icons.home_outlined, color: AppColors.textSecondary),
            selectedIcon: Icon(Icons.home, color: AppColors.primary),
            label: 'Home',
          ),
          // Wishlist - Guest restricted
          NavigationDestination(
            icon: Badge(
              label: wishlistProvider.favorites.isNotEmpty
                  ? Text(wishlistProvider.favorites.length.toString())
                  : null,
              isLabelVisible: wishlistProvider.favorites.isNotEmpty,
              backgroundColor: AppColors.favoriteRed,
              child: Icon(
                Icons.favorite_outline,
                color: isGuest ? AppColors.textLight : AppColors.textSecondary,
              ),
            ),
            selectedIcon: Icon(
              Icons.favorite,
              color: isGuest ? AppColors.textLight : AppColors.favoriteRed,
            ),
            label: 'Wishlist',
          ),
          // Cart - Guest restricted
          NavigationDestination(
            icon: Badge(
              label: cartProvider.itemCount > 0
                  ? Text(cartProvider.itemCount.toString())
                  : null,
              isLabelVisible: cartProvider.itemCount > 0,
              backgroundColor: AppColors.secondary,
              child: Icon(
                Icons.shopping_cart_outlined,
                color: isGuest ? AppColors.textLight : AppColors.textSecondary,
              ),
            ),
            selectedIcon: Icon(
              Icons.shopping_cart,
              color: isGuest ? AppColors.textLight : AppColors.primary,
            ),
            label: 'Cart',
          ),
          // Orders - Guest restricted
          const NavigationDestination(
            icon: Icon(
              Icons.receipt_long_outlined,
              color: AppColors.textSecondary,
            ),
            selectedIcon: Icon(Icons.receipt_long, color: AppColors.primary),
            label: 'Orders',
          ),
          // Profile - Guest restricted
          const NavigationDestination(
            icon: Icon(Icons.person_outline, color: AppColors.textSecondary),
            selectedIcon: Icon(Icons.person, color: AppColors.primary),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}
