import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:get_storage/get_storage.dart';

class StorageService {
  // Secure storage for sensitive data (token, user data)
  static const FlutterSecureStorage _secureStorage = FlutterSecureStorage();

  // GetStorage for simple preferences (theme, language, etc.)
  final GetStorage _box = GetStorage();

  // =============================================
  // TOKEN MANAGEMENT (Secure Storage)
  // =============================================

  // Save token
  Future<void> setToken(String token) async {
    try {
      await _secureStorage.write(key: 'auth_token', value: token);
      print('🟡 [Storage] Token saved successfully');
    } catch (e) {
      print('🔴 [Storage] Error saving token: $e');
    }
  }

  // Get token
  Future<String?> getToken() async {
    try {
      final token = await _secureStorage.read(key: 'auth_token');
      return token;
    } catch (e) {
      print('🔴 [Storage] Error getting token: $e');
      return null;
    }
  }

  // Delete token
  Future<void> deleteToken() async {
    try {
      await _secureStorage.delete(key: 'auth_token');
      print('🟡 [Storage] Token deleted');
    } catch (e) {
      print('🔴 [Storage] Error deleting token: $e');
    }
  }

  // =============================================
  // USER DATA (Secure Storage)
  // =============================================

  // Save user data as JSON string
  Future<void> setUser(String userJson) async {
    try {
      await _secureStorage.write(key: 'user_data', value: userJson);
    } catch (e) {
      print('🔴 [Storage] Error saving user: $e');
    }
  }

  // Get user data
  Future<String?> getUser() async {
    try {
      return await _secureStorage.read(key: 'user_data');
    } catch (e) {
      print('🔴 [Storage] Error getting user: $e');
      return null;
    }
  }

  // Delete user data
  Future<void> deleteUser() async {
    try {
      await _secureStorage.delete(key: 'user_data');
    } catch (e) {
      print('🔴 [Storage] Error deleting user: $e');
    }
  }

  // =============================================
  // GUEST MODE (Secure Storage)
  // =============================================

  Future<void> setGuestMode(bool isGuest) async {
    try {
      await _secureStorage.write(key: 'is_guest', value: isGuest.toString());
      print('🟡 [Storage] Guest mode set to: $isGuest');
    } catch (e) {
      print('🔴 [Storage] Error setting guest mode: $e');
    }
  }

  Future<bool> isGuestMode() async {
    try {
      final value = await _secureStorage.read(key: 'is_guest');
      if (value == null) {
        final token = await getToken();
        return token == null;
      }
      return value == 'true';
    } catch (e) {
      print('🔴 [Storage] Error getting guest mode: $e');
      return true;
    }
  }

  // =============================================
  // ONBOARDING MANAGEMENT (Secure Storage)
  // =============================================

  // ✅ Onboarding methods
  Future<bool> hasSeenOnboarding() async {
    return _box.read('has_seen_onboarding') ?? false;
  }

  Future<void> setOnboardingSeen(bool value) async {
    await _box.write('has_seen_onboarding', value);
  }

  // =============================================
  // CLEAR ALL (Secure Storage)
  // =============================================

  Future<void> clearAll() async {
    try {
      await _secureStorage.deleteAll();
      print('🟡 [Storage] All secure storage cleared');
    } catch (e) {
      print('🔴 [Storage] Error clearing secure storage: $e');
    }
  }

  // =============================================
  // GETSTORAGE (Simple Preferences)
  // =============================================

  // Theme preference
  void setThemeMode(String mode) {
    try {
      _box.write('theme_mode', mode);
    } catch (e) {
      print('🔴 [Storage] Error setting theme: $e');
    }
  }

  String getThemeMode() {
    try {
      return _box.read('theme_mode') ?? 'light';
    } catch (e) {
      return 'light';
    }
  }

  // Language preference
  void setLanguage(String lang) {
    try {
      _box.write('language', lang);
    } catch (e) {
      print('🔴 [Storage] Error setting language: $e');
    }
  }

  String getLanguage() {
    try {
      return _box.read('language') ?? 'en';
    } catch (e) {
      return 'en';
    }
  }

  // Recently viewed watches (store IDs)
  void setRecentlyViewed(List<String> ids) {
    try {
      _box.write('recently_viewed', ids);
    } catch (e) {
      print('🔴 [Storage] Error setting recently viewed: $e');
    }
  }

  List<String> getRecentlyViewed() {
    try {
      return _box.read('recently_viewed')?.cast<String>() ?? [];
    } catch (e) {
      return [];
    }
  }

  // =============================================
  // CHECK AUTH STATUS (Convenience)
  // =============================================

  Future<bool> isLoggedIn() async {
    try {
      final token = await getToken();
      return token != null && token.isNotEmpty;
    } catch (e) {
      print('🔴 [Storage] isLoggedIn error: $e');
      return false;
    }
  }

  // =============================================
  // CLEAR GETSTORAGE
  // =============================================

  void clearPreferences() {
    try {
      _box.erase();
      print('🟡 [Storage] Preferences cleared');
    } catch (e) {
      print('🔴 [Storage] Error clearing preferences: $e');
    }
  }

  // =============================================
  // FULL LOGOUT
  // =============================================

  Future<void> logout() async {
    try {
      await deleteToken();
      await deleteUser();
      await setGuestMode(false);
      clearPreferences();
      print('🟡 [Storage] Logout complete');
    } catch (e) {
      print('🔴 [Storage] Error during logout: $e');
    }
  }

  // =============================================
  // TEST STORAGE (For debugging)
  // =============================================

  Future<bool> testStorage() async {
    try {
      await _secureStorage.write(key: 'test_key', value: 'test_value');
      final test = await _secureStorage.read(key: 'test_key');
      await _secureStorage.delete(key: 'test_key');
      return test == 'test_value';
    } catch (e) {
      print('🔴 [Storage] Test failed: $e');
      return false;
    }
  }
}
