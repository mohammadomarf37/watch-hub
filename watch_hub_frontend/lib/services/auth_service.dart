import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:watch_hub_frontend/core/network/api_config.dart';
import 'package:watch_hub_frontend/core/network/api_exception.dart';
import 'package:watch_hub_frontend/models/user_profile.dart';

/// Handles all HTTP calls to the Laravel `/api/auth/*` endpoints.
///
/// Every method either returns parsed data on success or throws an
/// [ApiException] with a user-friendly message on failure.
class AuthService {
  Uri _uri(String path) => Uri.parse('${ApiConfig.baseUrl}$path');

  Map<String, String> _headers([String? token]) => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };

  /// POST /auth/register
  /// Returns a record of (user, token).
  Future<(UserProfile, String)> register({
    required String name,
    required String email,
    required String phone,
    required String password,
  }) async {
    final response = await http
        .post(
          _uri('/auth/register'),
          headers: _headers(),
          body: jsonEncode({
            'name': name,
            'email': email,
            'phone': phone,
            'password': password,
            // Backend requires the 'confirmed' rule; the signup screen
            // doesn't collect a separate confirm-password field, so we
            // confirm with the same value.
            'password_confirmation': password,
          }),
        )
        .timeout(ApiConfig.timeout);

    final body = _decode(response);
    _throwIfFailed(response, body);

    final data = body['data'] as Map<String, dynamic>;
    return (UserProfile.fromJson(data['user']), data['token'] as String);
  }

  /// POST /auth/login
  /// Returns a record of (user, token).
  Future<(UserProfile, String)> login({
    required String email,
    required String password,
  }) async {
    final response = await http
        .post(
          _uri('/auth/login'),
          headers: _headers(),
          body: jsonEncode({
            'email': email,
            'password': password,
          }),
        )
        .timeout(ApiConfig.timeout);

    final body = _decode(response);
    _throwIfFailed(response, body);

    final data = body['data'] as Map<String, dynamic>;
    return (UserProfile.fromJson(data['user']), data['token'] as String);
  }

  /// GET /auth/user — fetch the currently authenticated user using a stored token.
  Future<UserProfile> getCurrentUser(String token) async {
    final response = await http
        .get(_uri('/auth/user'), headers: _headers(token))
        .timeout(ApiConfig.timeout);

    final body = _decode(response);
    _throwIfFailed(response, body);

    return UserProfile.fromJson(body['data']);
  }

  /// POST /auth/logout — invalidates the token on the server.
  Future<void> logout(String token) async {
    final response = await http
        .post(_uri('/auth/logout'), headers: _headers(token))
        .timeout(ApiConfig.timeout);

    // Even if this fails (e.g. token already expired), we still clear the
    // local session in AuthProvider, so we don't throw here.
    _decode(response);
  }

  Map<String, dynamic> _decode(http.Response response) {
    if (response.body.isEmpty) return {};
    try {
      return jsonDecode(response.body) as Map<String, dynamic>;
    } catch (_) {
      throw ApiException(
        'Unexpected response from server (status ${response.statusCode}).',
        statusCode: response.statusCode,
      );
    }
  }

  void _throwIfFailed(http.Response response, Map<String, dynamic> body) {
    if (response.statusCode >= 200 && response.statusCode < 300) return;

    throw ApiException(
      body['message']?.toString() ?? 'Something went wrong. Please try again.',
      errors: body['errors'] as Map<String, dynamic>?,
      statusCode: response.statusCode,
    );
  }
}
