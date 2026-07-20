/// Thrown whenever an API call fails (validation error, wrong credentials,
/// network issue, server error, etc). Carries the message the UI should show.
class ApiException implements Exception {
  final String message;

  /// Field-level validation errors, e.g. {"email": ["The email has already been taken."]}
  final Map<String, dynamic>? errors;

  final int? statusCode;

  ApiException(this.message, {this.errors, this.statusCode});

  /// Returns the first validation error message if present, otherwise
  /// falls back to the general [message].
  String get displayMessage {
    if (errors != null && errors!.isNotEmpty) {
      final firstField = errors!.values.first;
      if (firstField is List && firstField.isNotEmpty) {
        return firstField.first.toString();
      }
    }
    return message;
  }

  @override
  String toString() => displayMessage;
}
