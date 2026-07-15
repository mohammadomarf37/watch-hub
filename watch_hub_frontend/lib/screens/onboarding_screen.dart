import 'package:flutter/material.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/routes/app_routes.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';
import 'package:watch_hub_frontend/core/widgets/custom_button.dart';

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentIndex = 0;

  final List<Map<String, String>> _onboardingData = [
    {
      'title': AppConstants.onboardingTitle1,
      'subtitle': AppConstants.onboardingSubtitle1,
      'image': 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=600&auto=format&fit=crop&q=80',
    },
    {
      'title': AppConstants.onboardingTitle2,
      'subtitle': AppConstants.onboardingSubtitle2,
      'image': 'https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?w=600&auto=format&fit=crop&q=80',
    },
    {
      'title': AppConstants.onboardingTitle3,
      'subtitle': AppConstants.onboardingSubtitle3,
      'image': 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=600&auto=format&fit=crop&q=80',
    },
  ];

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _onSkip() {
    Navigator.pushReplacementNamed(context, AppRoutes.login);
  }

  void _onNext() {
    if (_currentIndex < _onboardingData.length - 1) {
      _pageController.nextPage(
        duration: AppConstants.durationMedium,
        curve: Curves.easeInOut,
      );
    } else {
      Navigator.pushReplacementNamed(context, AppRoutes.login);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        actions: [
          if (_currentIndex < _onboardingData.length - 1)
            TextButton(
              onPressed: _onSkip,
              child: const Text(
                'Skip',
                style: TextStyle(
                  color: AppColors.textSecondary,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
        ],
      ),
      body: SafeArea(
        child: LayoutBuilder(
          builder: (context, constraints) {
            return Column(
              children: [
                Expanded(
                  child: PageView.builder(
                    controller: _pageController,
                    onPageChanged: (index) {
                      setState(() {
                        _currentIndex = index;
                      });
                    },
                    itemCount: _onboardingData.length,
                    itemBuilder: (context, index) {
                      final item = _onboardingData[index];
                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingLarge),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            // Beautiful Hero watch image container
                            Expanded(
                              flex: 3,
                              child: Container(
                                width: double.infinity,
                                margin: const EdgeInsets.only(bottom: AppConstants.paddingLarge),
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(AppConstants.radiusLarge),
                                  boxShadow: const [
                                    BoxShadow(
                                      color: AppColors.shadow,
                                      blurRadius: 15.0,
                                      offset: Offset(0.0, 5.0),
                                    ),
                                  ],
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(AppConstants.radiusLarge),
                                  child: WatchImage(
                                    imagePath: item['image'],
                                    fit: BoxFit.cover,
                                  ),
                                ),
                              ),
                            ),
                            // Title & Description
                            Expanded(
                              flex: 2,
                              child: Column(
                                children: [
                                  Text(
                                    item['title']!,
                                    textAlign: TextAlign.center,
                                    style: theme.textTheme.headlineMedium?.copyWith(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  AppConstants.spacingMedium,
                                  Text(
                                    item['subtitle']!,
                                    textAlign: TextAlign.center,
                                    style: theme.textTheme.bodyMedium?.copyWith(
                                      height: 1.5,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),
                // Footer (Indicators and Next Button)
                Padding(
                  padding: const EdgeInsets.all(AppConstants.paddingLarge),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Dots Indicator
                      Row(
                        children: List.generate(
                          _onboardingData.length,
                          (index) => AnimatedContainer(
                            duration: AppConstants.durationFast,
                            margin: const EdgeInsets.only(right: 6.0),
                            height: 8.0,
                            width: _currentIndex == index ? 24.0 : 8.0,
                            decoration: BoxDecoration(
                              color: _currentIndex == index ? AppColors.primary : AppColors.border,
                              borderRadius: BorderRadius.circular(AppConstants.radiusCircular),
                            ),
                          ),
                        ),
                      ),
                      // Action Button
                      CustomButton(
                        label: _currentIndex == _onboardingData.length - 1 ? 'GET STARTED' : 'NEXT',
                        onPressed: _onNext,
                        width: 140.0,
                        height: 48.0,
                      ),
                    ],
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
