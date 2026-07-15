import 'package:flutter/material.dart';

class AppConstants {
  AppConstants._();

  // Padding Constants
  static const double paddingXS = 4.0;
  static const double paddingSmall = 8.0;
  static const double paddingMedium = 16.0;
  static const double paddingLarge = 24.0;
  static const double paddingXL = 32.0;

  // Border Radius Constants
  static const double radiusXS = 4.0;
  static const double radiusSmall = 8.0;
  static const double radiusMedium = 12.0;
  static const double radiusLarge = 16.0;
  static const double radiusXL = 24.0;
  static const double radiusCircular = 100.0;

  // Animation Durations
  static const Duration durationFast = Duration(milliseconds: 200);
  static const Duration durationMedium = Duration(milliseconds: 300);
  static const Duration durationSlow = Duration(milliseconds: 500);

  // Common Spacing Widgets
  static const SizedBox spacingXS = SizedBox(height: 4.0, width: 4.0);
  static const SizedBox spacingSmall = SizedBox(height: 8.0, width: 8.0);
  static const SizedBox spacingMedium = SizedBox(height: 16.0, width: 16.0);
  static const SizedBox spacingLarge = SizedBox(height: 24.0, width: 24.0);
  static const SizedBox spacingXL = SizedBox(height: 32.0, width: 32.0);

  // String Constants
  static const String appName = 'WatchHub';
  static const String currencySymbol = '\$';
  
  // Onboarding Strings
  static const String onboardingTitle1 = 'Discover Premium Watches';
  static const String onboardingSubtitle1 = 'Explore a curated collection of world-class luxury watches from renowned brands.';
  
  static const String onboardingTitle2 = 'Refined Style & Detail';
  static const String onboardingSubtitle2 = 'Find the watch that defines your personality, built with timeless craftsmanship and modern tech.';
  
  static const String onboardingTitle3 = 'Seamless Shopping';
  static const String onboardingSubtitle3 = 'Enjoy secure ordering, easy package tracking, and direct customer support at your fingertips.';
}
