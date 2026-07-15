import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:watch_hub_frontend/models/user_profile.dart';

class AuthProvider extends ChangeNotifier {
  bool _isAuthenticated = false;
  bool _isLoading = false;
  bool _isGuest = false;
  UserProfile? _currentUser;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  bool get isGuest => _isGuest;
  bool get isLoggedIn => _isAuthenticated; // Alias as requested
  UserProfile? get currentUser => _currentUser;

  // Load session from SharedPreferences
  Future<void> loadSession() async {
    _isLoading = true;
    notifyListeners();

    try {
      final prefs = await SharedPreferences.getInstance();
      _isGuest = prefs.getBool('is_guest') ?? false;
      _isAuthenticated = prefs.getBool('is_logged_in') ?? false;

      if (_isGuest) {
        _currentUser = UserProfile(
          name: 'Guest User',
          email: 'guest@watchhub.com',
          phone: 'Not Available',
          avatarUrl: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
          address: 'Restricted in Guest Mode',
        );
      } else if (_isAuthenticated) {
        final email = prefs.getString('user_email') ?? 'omar@watchhub.com';
        final name = prefs.getString('user_name') ?? 'Omar Farooq';
        final phone = prefs.getString('user_phone') ?? '+1 (555) 019-2834';
        final address = prefs.getString('user_address') ?? '123 Luxury Watch Blvd, Suite 100, New York, NY 10001';

        _currentUser = UserProfile(
          name: name,
          email: email,
          phone: phone,
          avatarUrl: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150',
          address: address,
        );
      }
    } catch (e) {
      debugPrint('Error loading auth session: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Simulate login as guest
  Future<void> loginAsGuest() async {
    _isLoading = true;
    notifyListeners();

    // Simulate network latency
    await Future.delayed(const Duration(milliseconds: 500));

    _isGuest = true;
    _isAuthenticated = false;
    _currentUser = UserProfile(
      name: 'Guest User',
      email: 'guest@watchhub.com',
      phone: 'Not Available',
      avatarUrl: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
      address: 'Restricted in Guest Mode',
    );

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('is_guest', true);
      await prefs.setBool('is_logged_in', false);
    } catch (e) {
      debugPrint('Error saving guest session: $e');
    }

    _isLoading = false;
    notifyListeners();
  }

  // Simulate login
  Future<bool> login(String email, String password) async {
    _isLoading = true;
    notifyListeners();

    // Simulate network latency
    await Future.delayed(const Duration(milliseconds: 1500));

    // Dummy logic: Accept any input that passes form validation
    _currentUser = UserProfile(
      name: 'Omar Farooq',
      email: email,
      phone: '+1 (555) 019-2834',
      avatarUrl: 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=150',
      address: '123 Luxury Watch Blvd, Suite 100, New York, NY 10001',
    );
    _isAuthenticated = true;
    _isGuest = false;
    _isLoading = false;

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('is_guest', false);
      await prefs.setBool('is_logged_in', true);
      await prefs.setString('user_email', email);
      await prefs.setString('user_name', _currentUser!.name);
      await prefs.setString('user_phone', _currentUser!.phone);
      await prefs.setString('user_address', _currentUser!.address);
    } catch (e) {
      debugPrint('Error saving login session: $e');
    }

    notifyListeners();
    return true;
  }

  // Simulate signup
  Future<bool> signup(String name, String email, String phone, String password) async {
    _isLoading = true;
    notifyListeners();

    // Simulate network latency
    await Future.delayed(const Duration(milliseconds: 1500));

    _currentUser = UserProfile(
      name: name,
      email: email,
      phone: phone,
      avatarUrl: 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
      address: 'Add your shipping address in Profile settings.',
    );
    _isAuthenticated = true;
    _isGuest = false;
    _isLoading = false;

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('is_guest', false);
      await prefs.setBool('is_logged_in', true);
      await prefs.setString('user_email', email);
      await prefs.setString('user_name', name);
      await prefs.setString('user_phone', phone);
      await prefs.setString('user_address', _currentUser!.address);
    } catch (e) {
      debugPrint('Error saving signup session: $e');
    }

    notifyListeners();
    return true;
  }

  // Simulate logout
  void logout() async {
    _isAuthenticated = false;
    _isGuest = false;
    _currentUser = null;

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.clear();
    } catch (e) {
      debugPrint('Error clearing session: $e');
    }

    notifyListeners();
  }

  // Update profile locally
  void updateProfile(UserProfile updatedProfile) async {
    _currentUser = updatedProfile;
    
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('user_name', updatedProfile.name);
      await prefs.setString('user_email', updatedProfile.email);
      await prefs.setString('user_phone', updatedProfile.phone);
      await prefs.setString('user_address', updatedProfile.address);
    } catch (e) {
      debugPrint('Error updating profile session: $e');
    }

    notifyListeners();
  }
}

// Type alias for compatibility if referenced as AuthController
typedef AuthController = AuthProvider;

