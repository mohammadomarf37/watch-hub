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
  
  const MainLayout({
    super.key,
    this.initialIndex = 0,
  });

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

  @override
  Widget build(BuildContext context) {
    final cartProvider = Provider.of<CartProvider>(context);
    final wishlistProvider = Provider.of<WishlistProvider>(context);

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _screens,
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _currentIndex,
        onDestinationSelected: (index) {
          final authProvider = Provider.of<AuthProvider>(context, listen: false);
          if (index != 0 && authProvider.isGuest) {
            showGuestDialog(context);
          } else {
            setState(() {
              _currentIndex = index;
            });
          }
        },
        backgroundColor: Colors.white,
        indicatorColor: AppColors.primary.withOpacity(0.08),
        elevation: 10.0,
        destinations: [
          // Home
          const NavigationDestination(
            icon: Icon(Icons.home_outlined, color: AppColors.textSecondary),
            selectedIcon: Icon(Icons.home, color: AppColors.primary),
            label: 'Home',
          ),
          // Wishlist
          NavigationDestination(
            icon: Badge(
              label: wishlistProvider.favorites.isNotEmpty
                  ? Text(wishlistProvider.favorites.length.toString())
                  : null,
              isLabelVisible: wishlistProvider.favorites.isNotEmpty,
              backgroundColor: AppColors.favoriteRed,
              child: const Icon(Icons.favorite_outline, color: AppColors.textSecondary),
            ),
            selectedIcon: const Icon(Icons.favorite, color: AppColors.favoriteRed),
            label: 'Wishlist',
          ),
          // Cart
          NavigationDestination(
            icon: Badge(
              label: cartProvider.itemCount > 0
                  ? Text(cartProvider.itemCount.toString())
                  : null,
              isLabelVisible: cartProvider.itemCount > 0,
              backgroundColor: AppColors.secondary,
              child: const Icon(Icons.shopping_cart_outlined, color: AppColors.textSecondary),
            ),
            selectedIcon: const Icon(Icons.shopping_cart, color: AppColors.primary),
            label: 'Cart',
          ),
          // Orders
          const NavigationDestination(
            icon: Icon(Icons.receipt_long_outlined, color: AppColors.textSecondary),
            selectedIcon: Icon(Icons.receipt_long, color: AppColors.primary),
            label: 'Orders',
          ),
          // Profile
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
