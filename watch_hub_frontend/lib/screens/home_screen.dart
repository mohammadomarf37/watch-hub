import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:watch_hub_frontend/core/constants/app_colors.dart';
import 'package:watch_hub_frontend/core/constants/app_constants.dart';
import 'package:watch_hub_frontend/core/widgets/empty_state.dart';
import 'package:watch_hub_frontend/core/widgets/shimmer_loader.dart';
import 'package:watch_hub_frontend/core/widgets/watch_card.dart';
import 'package:watch_hub_frontend/models/watch.dart';
import 'package:watch_hub_frontend/providers/auth_provider.dart';
import 'package:watch_hub_frontend/providers/product_provider.dart';
import 'package:watch_hub_frontend/core/utils/image_helper.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  // Show filter bottom sheet
  void _showFilters(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(AppConstants.radiusXL)),
      ),
      builder: (context) {
        return const FilterBottomSheet();
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final authProvider = Provider.of<AuthProvider>(context);
    final productProvider = Provider.of<ProductProvider>(context);

    final userName = authProvider.currentUser?.name.split(' ').first ?? 'Guest';
    final filteredWatches = productProvider.filteredWatches;
    final isSearching = productProvider.searchQuery.isNotEmpty || 
        productProvider.selectedBrands.isNotEmpty || 
        productProvider.selectedCategories.isNotEmpty ||
        productProvider.minRating > 0.0 ||
        productProvider.priceRange.start > 0.0 ||
        productProvider.priceRange.end < 15000.0;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: authProvider.isGuest
          ? AppBar(
              backgroundColor: AppColors.background,
              elevation: 0.0,
              centerTitle: true,
              automaticallyImplyLeading: false,
              title: Chip(
                avatar: const Icon(Icons.person, size: 16, color: AppColors.primary),
                label: const Text(
                  'Guest Mode',
                  style: TextStyle(
                    color: AppColors.primary,
                    fontWeight: FontWeight.bold,
                    fontSize: 12.0,
                  ),
                ),
                backgroundColor: AppColors.primary.withOpacity(0.08),
                side: BorderSide(color: AppColors.primary.withOpacity(0.2)),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
            )
          : null,
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: () => productProvider.refreshProducts(),
          color: AppColors.primary,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.symmetric(vertical: AppConstants.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // 1. Header (Greeting & Notification Icon)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Hello, $userName',
                            style: theme.textTheme.bodyMedium?.copyWith(
                              fontWeight: FontWeight.w500,
                              color: AppColors.textSecondary,
                            ),
                          ),
                          Text(
                            'Find your timepiece',
                            style: theme.textTheme.headlineMedium?.copyWith(
                              color: AppColors.primary,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      IconButton(
                        icon: const Badge(
                          label: Text('2'),
                          backgroundColor: AppColors.secondary,
                          child: Icon(Icons.notifications_none_outlined, size: 28.0),
                        ),
                        onPressed: () {
                          Navigator.pushNamed(context, '/notifications');
                        },
                      ),
                    ],
                  ),
                ),
                AppConstants.spacingMedium,

                // 2. Search & Filter Row
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                  child: Row(
                    children: [
                      Expanded(
                        child: Container(
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                            border: Border.all(color: AppColors.border),
                          ),
                          child: TextField(
                            controller: _searchController,
                            onChanged: (val) {
                              productProvider.setSearchQuery(val);
                            },
                            decoration: InputDecoration(
                              hintText: 'Search brand, model, features...',
                              prefixIcon: const Icon(Icons.search, color: AppColors.textSecondary),
                              suffixIcon: _searchController.text.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.clear, color: AppColors.textSecondary),
                                      onPressed: () {
                                        _searchController.clear();
                                        productProvider.setSearchQuery('');
                                      },
                                    )
                                  : null,
                              border: InputBorder.none,
                              contentPadding: const EdgeInsets.symmetric(vertical: 12.0),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12.0),
                      GestureDetector(
                        onTap: () => _showFilters(context),
                        child: Container(
                          padding: const EdgeInsets.all(12.0),
                          decoration: BoxDecoration(
                            color: AppColors.primary,
                            borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                          ),
                          child: const Icon(
                            Icons.tune,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                AppConstants.spacingMedium,

                // If searching, show search result view
                if (isSearching) ...[
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          'Search Results (${filteredWatches.length})',
                          style: theme.textTheme.titleMedium,
                        ),
                        TextButton(
                          onPressed: () {
                            _searchController.clear();
                            productProvider.resetFilters();
                          },
                          child: const Text('Clear Filters'),
                        ),
                      ],
                    ),
                  ),
                  productProvider.isLoading
                      ? ShimmerLoader.buildGridLoader()
                      : filteredWatches.isEmpty
                          ? const EmptyState(
                              icon: Icons.search_off_outlined,
                              title: 'No Watches Found',
                              description: 'Try adjusting your search terms or filter constraints.',
                            )
                          : GridView.builder(
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                              padding: const EdgeInsets.all(AppConstants.paddingMedium),
                              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: 2,
                                mainAxisSpacing: AppConstants.paddingMedium,
                                crossAxisSpacing: AppConstants.paddingMedium,
                                childAspectRatio: 0.72,
                              ),
                              itemCount: filteredWatches.length,
                              itemBuilder: (context, index) {
                                return WatchCard(watch: filteredWatches[index]);
                              },
                            ),
                ] else ...[
                  // 3. Banner Promos Slider
                  SizedBox(
                    height: 150.0,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                      children: [
                        _buildPromoBanner(
                          'Summer Gold Collection',
                          'Up to 15% discount on golden designs',
                          'https://images.unsplash.com/photo-1614162692292-7ac56d7f7f1e?w=600',
                          theme,
                        ),
                        _buildPromoBanner(
                          'The Smart Revolution',
                          'Explore Apple Ultra & Galaxy Series',
                          'https://images.unsplash.com/photo-1509048191080-d2984bad6ae5?w=600',
                          theme,
                        ),
                      ],
                    ),
                  ),
                  AppConstants.spacingLarge,

                  // 4. Categories Selection Row
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                    child: Text(
                      'Categories',
                      style: theme.textTheme.titleLarge?.copyWith(
                        color: AppColors.primary,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8.0),
                  SizedBox(
                    height: 40.0,
                    child: ListView.builder(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                      itemCount: productProvider.categories.length + 1,
                      itemBuilder: (context, index) {
                        final isAll = index == 0;
                        final categoryName = isAll ? 'All' : productProvider.categories[index - 1];
                        final isSelected = isAll
                            ? productProvider.selectedCategories.isEmpty
                            : productProvider.selectedCategories.contains(categoryName);

                        return Padding(
                          padding: const EdgeInsets.only(right: 8.0),
                          child: ChoiceChip(
                            label: Text(categoryName),
                            selected: isSelected,
                            selectedColor: AppColors.primary,
                            backgroundColor: AppColors.surface,
                            labelStyle: TextStyle(
                              color: isSelected ? Colors.white : AppColors.textSecondary,
                              fontWeight: FontWeight.w600,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(AppConstants.radiusSmall),
                              side: BorderSide(color: isSelected ? AppColors.primary : AppColors.border),
                            ),
                            onSelected: (selected) {
                              if (isAll) {
                                productProvider.resetFilters();
                              } else {
                                productProvider.toggleCategory(categoryName);
                              }
                            },
                          ),
                        );
                      },
                    ),
                  ),
                  AppConstants.spacingLarge,

                  // 5. Featured Watches Section
                  _buildProductSection(
                    context,
                    'Featured Watches',
                    productProvider.allWatches.where((w) => w.isFeatured).toList(),
                    productProvider.isLoading,
                  ),
                  AppConstants.spacingLarge,

                  // 6. Popular Watches Section
                  _buildProductSection(
                    context,
                    'Popular Timepieces',
                    productProvider.allWatches.where((w) => w.isPopular).toList(),
                    productProvider.isLoading,
                  ),
                  AppConstants.spacingLarge,

                  // 7. New Arrivals Section
                  _buildProductSection(
                    context,
                    'New Arrivals',
                    productProvider.allWatches.where((w) => w.isNewArrival).toList(),
                    productProvider.isLoading,
                  ),
                  AppConstants.spacingLarge,

                  // 8. Recommended Watches Section
                  _buildProductSection(
                    context,
                    'Recommended for You',
                    productProvider.allWatches.where((w) => w.isRecommended).toList(),
                    productProvider.isLoading,
                  ),
                  AppConstants.spacingLarge,

                  // 9. Recently Viewed Watches Section
                  if (productProvider.recentlyViewed.isNotEmpty) ...[
                    _buildProductSection(
                      context,
                      'Recently Viewed',
                      productProvider.recentlyViewed,
                      false,
                    ),
                    AppConstants.spacingLarge,
                  ],
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPromoBanner(String title, String subtitle, String imageUrl, ThemeData theme) {
    return Container(
      width: 300.0,
      margin: const EdgeInsets.only(right: 12.0),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
      ),
      child: Stack(
        children: [
          // Banner Background Image with overlay
          Positioned.fill(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(AppConstants.radiusMedium),
              child: Opacity(
                opacity: 0.4,
                child: WatchImage(imagePath: imageUrl),
              ),
            ),
          ),
          // Banner Texts
          Padding(
            padding: const EdgeInsets.all(AppConstants.paddingMedium),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18.0,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4.0),
                Text(
                  subtitle,
                  style: const TextStyle(
                    color: AppColors.secondary,
                    fontSize: 12.0,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProductSection(
    BuildContext context,
    String title,
    List<Watch> list,
    bool isLoading,
  ) {
    final theme = Theme.of(context);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
          child: Text(
            title,
            style: theme.textTheme.titleLarge?.copyWith(
              color: AppColors.primary,
            ),
          ),
        ),
        const SizedBox(height: 12.0),
        SizedBox(
          height: 250.0,
          child: isLoading
              ? ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                  itemCount: 3,
                  itemBuilder: (context, index) {
                    return Container(
                      width: 170.0,
                      margin: const EdgeInsets.only(right: AppConstants.paddingMedium),
                      child: ShimmerLoader.buildGridLoader(count: 1, aspectRatio: 0.72),
                    );
                  },
                )
              : ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: AppConstants.paddingMedium),
                  itemCount: list.length,
                  itemBuilder: (context, index) {
                    return Container(
                      width: 170.0,
                      margin: const EdgeInsets.only(right: AppConstants.paddingMedium),
                      child: WatchCard(watch: list[index]),
                    );
                  },
                ),
        ),
      ],
    );
  }
}

// MODERN FILTER BOTTOM SHEET WIDGET
class FilterBottomSheet extends StatefulWidget {
  const FilterBottomSheet({super.key});

  @override
  State<FilterBottomSheet> createState() => _FilterBottomSheetState();
}

class _FilterBottomSheetState extends State<FilterBottomSheet> {
  late List<String> _tempBrands;
  late List<String> _tempCategories;
  late RangeValues _tempPriceRange;
  late double _tempMinRating;
  late String _tempSortBy;

  @override
  void initState() {
    super.initState();
    final p = Provider.of<ProductProvider>(context, listen: false);
    _tempBrands = List.from(p.selectedBrands);
    _tempCategories = List.from(p.selectedCategories);
    _tempPriceRange = p.priceRange;
    _tempMinRating = p.minRating;
    _tempSortBy = p.sortBy;
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final productProvider = Provider.of<ProductProvider>(context, listen: false);

    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.8,
      maxChildSize: 0.9,
      minChildSize: 0.5,
      builder: (context, scrollController) {
        return SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(AppConstants.paddingLarge),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Indicator line
              Center(
                child: Container(
                  width: 40.0,
                  height: 4.0,
                  decoration: BoxDecoration(
                    color: AppColors.border,
                    borderRadius: BorderRadius.circular(2.0),
                  ),
                ),
              ),
              AppConstants.spacingMedium,
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'Filter & Sort',
                    style: theme.textTheme.titleLarge,
                  ),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _tempBrands.clear();
                        _tempCategories.clear();
                        _tempPriceRange = const RangeValues(0.0, 15000.0);
                        _tempMinRating = 0.0;
                        _tempSortBy = 'Popularity';
                      });
                    },
                    child: const Text('Reset All'),
                  ),
                ],
              ),
              const Divider(),
              AppConstants.spacingMedium,

              // 1. Sort By
              Text('Sort By', style: theme.textTheme.titleSmall),
              const SizedBox(height: 8.0),
              Wrap(
                spacing: 8.0,
                runSpacing: 4.0,
                children: ['Popularity', 'Newest', 'Price Low → High', 'Price High → Low'].map((opt) {
                  final isSelected = _tempSortBy == opt;
                  return ChoiceChip(
                    label: Text(opt),
                    selected: isSelected,
                    selectedColor: AppColors.primary,
                    backgroundColor: AppColors.surface,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : AppColors.textSecondary,
                    ),
                    onSelected: (selected) {
                      setState(() {
                        _tempSortBy = opt;
                      });
                    },
                  );
                }).toList(),
              ),
              AppConstants.spacingMedium,

              // 2. Brands
              Text('Brands', style: theme.textTheme.titleSmall),
              const SizedBox(height: 8.0),
              Wrap(
                spacing: 8.0,
                runSpacing: 4.0,
                children: productProvider.brands.map((brand) {
                  final isSelected = _tempBrands.contains(brand);
                  return FilterChip(
                    label: Text(brand),
                    selected: isSelected,
                    selectedColor: AppColors.primary,
                    checkmarkColor: Colors.white,
                    backgroundColor: AppColors.surface,
                    labelStyle: TextStyle(
                      color: isSelected ? Colors.white : AppColors.textSecondary,
                    ),
                    onSelected: (selected) {
                      setState(() {
                        if (isSelected) {
                          _tempBrands.remove(brand);
                        } else {
                          _tempBrands.add(brand);
                        }
                      });
                    },
                  );
                }).toList(),
              ),
              AppConstants.spacingMedium,

              // 3. Price Range Slider
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text('Price Range', style: theme.textTheme.titleSmall),
                  Text(
                    '\$${_tempPriceRange.start.toStringAsFixed(0)} - \$${_tempPriceRange.end.toStringAsFixed(0)}',
                    style: theme.textTheme.titleSmall?.copyWith(color: AppColors.primary),
                  ),
                ],
              ),
              RangeSlider(
                values: _tempPriceRange,
                min: 0.0,
                max: 15000.0,
                divisions: 150,
                activeColor: AppColors.primary,
                inactiveColor: AppColors.border,
                onChanged: (values) {
                  setState(() {
                    _tempPriceRange = values;
                  });
                },
              ),
              AppConstants.spacingMedium,

              // 4. Rating Selector
              Text('Minimum Rating', style: theme.textTheme.titleSmall),
              const SizedBox(height: 8.0),
              Row(
                children: List.generate(5, (index) {
                  final starVal = index + 1.0;
                  return IconButton(
                    icon: Icon(
                      _tempMinRating >= starVal ? Icons.star : Icons.star_border,
                      color: AppColors.secondary,
                      size: 32.0,
                    ),
                    onPressed: () {
                      setState(() {
                        if (_tempMinRating == starVal) {
                          _tempMinRating = 0.0;
                        } else {
                          _tempMinRating = starVal;
                        }
                      });
                    },
                  );
                }),
              ),
              AppConstants.spacingLarge,

              // Apply Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () {
                    // Save filters back to provider
                    productProvider.setSortBy(_tempSortBy);
                    productProvider.setMinRating(_tempMinRating);
                    productProvider.setPriceRange(_tempPriceRange);

                    // Reset and sync brand filters
                    productProvider.resetFilters(); // resets first, let's just write setters to avoid conflict
                    
                    // Direct mutations
                    productProvider.setSearchQuery('');
                    // For brands/categories list update:
                    for (var brand in _tempBrands) {
                      productProvider.toggleBrand(brand);
                    }
                    for (var cat in _tempCategories) {
                      productProvider.toggleCategory(cat);
                    }

                    productProvider.setSortBy(_tempSortBy);
                    productProvider.setMinRating(_tempMinRating);
                    productProvider.setPriceRange(_tempPriceRange);

                    Navigator.pop(context);
                  },
                  child: const Padding(
                    padding: EdgeInsets.symmetric(vertical: 14.0),
                    child: Text('APPLY FILTERS'),
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
