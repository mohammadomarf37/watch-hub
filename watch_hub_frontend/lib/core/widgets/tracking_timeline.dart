import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/models/order.dart';

class TrackingTimeline extends StatelessWidget {
  final List<TrackingStep> steps;

  const TrackingTimeline({
    super.key,
    required this.steps,
  });

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      physics: const NeverScrollableScrollPhysics(),
      shrinkWrap: true,
      itemCount: steps.length,
      itemBuilder: (context, index) {
        final step = steps[index];
        final isLast = index == steps.length - 1;
        final isCompleted = step.isCompleted;

        return IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Timeline Column (Dot and Line)
              Column(
                children: [
                  // Dot Node
                  Container(
                    width: 24.0,
                    height: 24.0,
                    decoration: BoxDecoration(
                      color: isCompleted ? AppColors.primary : Colors.white,
                      border: Border.all(
                        color: isCompleted ? AppColors.primary : AppColors.border,
                        width: 2.0,
                      ),
                      shape: BoxShape.circle,
                    ),
                    child: isCompleted
                        ? const Icon(
                            Icons.check,
                            color: Colors.white,
                            size: 14.0,
                          )
                        : null,
                  ),
                  // Connecting Line
                  if (!isLast)
                    Expanded(
                      child: Container(
                        width: 2.0,
                        color: isCompleted ? AppColors.primary : AppColors.border,
                      ),
                    ),
                ],
              ),
              AppConstants.spacingMedium,
              // Details Column
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.only(bottom: AppConstants.paddingLarge),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        step.title,
                        style: TextStyle(
                          fontSize: 15.0,
                          fontWeight: FontWeight.bold,
                          color: isCompleted ? AppColors.textPrimary : AppColors.textLight,
                        ),
                      ),
                      AppConstants.spacingXS,
                      Text(
                        step.description,
                        style: TextStyle(
                          fontSize: 13.0,
                          color: isCompleted ? AppColors.textSecondary : AppColors.textLight,
                          height: 1.3,
                        ),
                      ),
                      AppConstants.spacingXS,
                      Text(
                        DateFormat('MMM dd, yyyy - hh:mm a').format(step.time),
                        style: const TextStyle(
                          fontSize: 11.0,
                          color: AppColors.textLight,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
