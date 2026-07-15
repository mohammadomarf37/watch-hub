import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/providers/profile_provider.dart';

class FAQScreen extends StatelessWidget {
  const FAQScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final profileProvider = Provider.of<ProfileProvider>(context);
    final faqs = profileProvider.faqs;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Frequently Asked Questions'),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SafeArea(
        child: ListView.builder(
          padding: const EdgeInsets.all(AppConstants.paddingMedium),
          itemCount: faqs.length,
          itemBuilder: (context, index) {
            final faq = faqs[index];

            return Container(
              margin: const EdgeInsets.only(bottom: AppConstants.paddingMedium),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
                border: Border.all(color: AppColors.border),
                boxShadow: const [
                  BoxShadow(
                    color: AppColors.shadow,
                    offset: Offset(0.0, 2.0),
                    blurRadius: 4.0,
                  ),
                ],
              ),
              child: ExpansionTile(
                title: Text(
                  faq.question,
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                    fontSize: 14.5,
                  ),
                ),
                shape: const Border(), // Removes bottom line during expansion
                childrenPadding: const EdgeInsets.all(AppConstants.paddingMedium),
                expandedAlignment: Alignment.topLeft,
                iconColor: AppColors.primary,
                collapsedIconColor: AppColors.textSecondary,
                children: [
                  Text(
                    faq.answer,
                    style: const TextStyle(
                      color: AppColors.textSecondary,
                      height: 1.5,
                      fontSize: 13.5,
                    ),
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
