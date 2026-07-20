import 'dart:async';
import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:watch_hub_frontend/core/network/api_config.dart';
import 'package:watch_hub_frontend/models/user_profile.dart';
import 'package:watch_hub_frontend/services/storage_service.dart';

class AuthProvider extends ChangeNotifier {
  final Dio _dio = Dio();
  final StorageService _storage = StorageService();

  bool _isAuthenticated = false;
  bool _isLoading = false;
  bool _isGuest = false;
  UserProfile? _currentUser;
  String? _token;
  String? _errorMessage;

  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  bool get isGuest => _isGuest;
  bool get isLoggedIn => _isAuthenticated;
  UserProfile? get currentUser => _currentUser;
  String? get token => _token;
  String? get errorMessage => _errorMessage;

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  // =============================================
  // LOAD SESSION
  // =============================================

  Future<void> loadSession() async {
    _isLoading = true;
    notifyListeners();

    try {
      _isGuest = await _storage.isGuestMode();
      final storedToken = await _storage.getToken();

      print(
        '🟡 [AUTH] loadSession - Token: ${storedToken != null ? "Exists" : "NULL"}',
      );
      print('🟡 [AUTH] loadSession - Guest: $_isGuest');

      if (_isGuest) {
        _currentUser = UserProfile(
          name: 'Guest User',
          email: 'guest@watchhub.com',
          phone: 'Not Available',
          avatarUrl:
              'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
          address: 'Restricted in Guest Mode',
        );
        _isAuthenticated = false;
        _token = null;
      } else if (storedToken != null && storedToken.isNotEmpty) {
        _token = storedToken;
        _isAuthenticated = true;

        // Try to get user info
        try {
          final user = await _getCurrentUser(storedToken);
          _currentUser = user;
          print('🟡 [AUTH] User loaded: ${user.name}');
        } catch (e) {
          print('🔴 [AUTH] Failed to load user: $e');
          // Token might be invalid, but keep authenticated state
          _currentUser = UserProfile(
            name: 'User',
            email: 'user@watchhub.com',
            phone: '',
            avatarUrl: '',
            address: '',
          );
        }
      } else {
        _isAuthenticated = false;
        _token = null;
        _currentUser = null;
      }
    } catch (e) {
      print('🔴 [AUTH] loadSession error: $e');
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // =============================================
  // LOGIN
  // =============================================

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      print('🟡 [AUTH] Login attempt for: $email');

      final response = await _dio.post(
        ApiConfig.login,
        data: {'email': email, 'password': password},
      );

      print('🟡 [AUTH] Login response status: ${response.statusCode}');

      if (response.statusCode == 200) {
        final data = response.data;
        final token = data['data']['token'] ?? data['token'];
        final userData = data['data']['user'] ?? data['user'];

        if (token == null) {
          _errorMessage = 'Invalid response from server';
          _isLoading = false;
          notifyListeners();
          return false;
        }

        print('🟡 [AUTH] Token received: ${token.substring(0, 20)}...');

        // Save token
        await _storage.setToken(token);
        await _storage.setGuestMode(false);

        // Verify token saved
        final savedToken = await _storage.getToken();
        print(
          '🟡 [AUTH] Token saved: ${savedToken != null ? "SUCCESS" : "FAILED"}',
        );

        _token = token;
        _isAuthenticated = true;
        _isGuest = false;
        _currentUser = UserProfile.fromJson(userData);

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = 'Login failed. Please try again.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } on DioException catch (e) {
      print('🔴 [AUTH] Login Dio error: ${e.message}');
      print('🔴 [AUTH] Response: ${e.response?.data}');

      if (e.response?.statusCode == 401) {
        _errorMessage = 'Invalid email or password';
      } else if (e.response?.statusCode == 422) {
        final errors = e.response?.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = 'Validation failed';
        }
      } else {
        _errorMessage = 'Network error. Please try again.';
      }

      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      print('🔴 [AUTH] Login error: $e');
      _errorMessage = 'Something went wrong. Please try again.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // =============================================
  // REGISTER
  // =============================================

  Future<bool> register(
    String name,
    String email,
    String phone,
    String password,
  ) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      print('🟡 [AUTH] Register attempt for: $email');

      final response = await _dio.post(
        ApiConfig.register,
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'password': password,
          'password_confirmation': password,
        },
      );

      if (response.statusCode == 201 || response.statusCode == 200) {
        final data = response.data;
        final token = data['data']['token'] ?? data['token'];
        final userData = data['data']['user'] ?? data['user'];

        if (token == null) {
          _errorMessage = 'Invalid response from server';
          _isLoading = false;
          notifyListeners();
          return false;
        }

        await _storage.setToken(token);
        await _storage.setGuestMode(false);

        _token = token;
        _isAuthenticated = true;
        _isGuest = false;
        _currentUser = UserProfile.fromJson(userData);

        _isLoading = false;
        notifyListeners();
        return true;
      } else {
        _errorMessage = 'Registration failed. Please try again.';
        _isLoading = false;
        notifyListeners();
        return false;
      }
    } on DioException catch (e) {
      print('🔴 [AUTH] Register Dio error: ${e.message}');

      if (e.response?.statusCode == 422) {
        final errors = e.response?.data['errors'];
        if (errors != null) {
          _errorMessage = errors.values.first.first;
        } else {
          _errorMessage = 'Validation failed';
        }
      } else {
        _errorMessage = 'Network error. Please try again.';
      }

      _isLoading = false;
      notifyListeners();
      return false;
    } catch (e) {
      print('🔴 [AUTH] Register error: $e');
      _errorMessage = 'Something went wrong. Please try again.';
      _isLoading = false;
      notifyListeners();
      return false;
    }
  }

  // =============================================
  // LOGOUT
  // =============================================

  Future<void> logout() async {
    final currentToken = _token;

    _isAuthenticated = false;
    _isGuest = false;
    _currentUser = null;
    _token = null;

    await _storage.logout();
    notifyListeners();

    if (currentToken != null) {
      try {
        await _dio.post(
          ApiConfig.logout,
          options: Options(headers: {'Authorization': 'Bearer $currentToken'}),
        );
        print('🟡 [AUTH] Server logout successful');
      } catch (e) {
        print('🔴 [AUTH] Server logout error: $e');
      }
    }
  }

  // =============================================
  // GUEST LOGIN
  // =============================================

  Future<void> loginAsGuest() async {
    _isLoading = true;
    notifyListeners();

    _isGuest = true;
    _isAuthenticated = false;
    _token = null;
    _currentUser = UserProfile(
      name: 'Guest User',
      email: 'guest@watchhub.com',
      phone: 'Not Available',
      avatarUrl:
          'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150',
      address: 'Restricted in Guest Mode',
    );

    await _storage.setGuestMode(true);
    await _storage.deleteToken();

    _isLoading = false;
    notifyListeners();
  }

  // =============================================
  // GET CURRENT USER
  // =============================================

  Future<UserProfile> _getCurrentUser(String token) async {
    final response = await _dio.get(
      ApiConfig.userProfile,
      options: Options(headers: {'Authorization': 'Bearer $token'}),
    );

    if (response.statusCode == 200) {
      final data = response.data;
      return UserProfile.fromJson(data['data'] ?? data['user']);
    } else {
      throw Exception('Failed to get user');
    }
  }

  // =============================================
  // UPDATE PROFILE
  // =============================================

  void updateProfile(UserProfile updatedProfile) {
    _currentUser = updatedProfile;
    notifyListeners();
  }

  // =============================================
  // UPDATE PROFILE ON SERVER
  // =============================================

  Future<bool> updateProfileOnServer(Map<String, dynamic> data) async {
    try {
      final token = await _storage.getToken();
      if (token == null) return false;

      final response = await _dio.put(
        ApiConfig.profile,
        data: data,
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      if (response.statusCode == 200) {
        final userData = response.data['data'] ?? response.data['user'];
        _currentUser = UserProfile.fromJson(userData);
        notifyListeners();
        return true;
      }
      return false;
    } catch (e) {
      print('🔴 [AUTH] Update profile error: $e');
      return false;
    }
  }

  // =============================================
  // CHANGE PASSWORD
  // =============================================

  Future<bool> changePassword(
    String currentPassword,
    String newPassword,
  ) async {
    try {
      final token = await _storage.getToken();
      if (token == null) return false;

      final response = await _dio.post(
        ApiConfig.changePassword,
        data: {
          'current_password': currentPassword,
          'password': newPassword,
          'password_confirmation': newPassword,
        },
        options: Options(headers: {'Authorization': 'Bearer $token'}),
      );

      return response.statusCode == 200;
    } catch (e) {
      print('🔴 [AUTH] Change password error: $e');
      return false;
    }
  }
}
