import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';

void showGuestDialog(BuildContext context) {
  showDialog(
    context: context,
    barrierDismissible: true,
    builder: (context) => AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      title: Row(
        children: [
          Icon(Icons.lock_outline, color: AppColors.primary),
          const SizedBox(width: 8),
          const Text('Guest Mode'),
        ],
      ),
      content: const Text(
        'Please login or create an account to access this feature.',
        style: TextStyle(fontSize: 14),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.pop(context),
          child: const Text('Cancel'),
        ),
        ElevatedButton(
          onPressed: () {
            Navigator.pop(context);
            Navigator.pushReplacementNamed(context, '/login');
          },
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
          ),
          child: const Text('Login'),
        ),
      ],
    ),
  );
}
