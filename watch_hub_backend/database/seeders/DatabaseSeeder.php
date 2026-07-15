<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@watchhub.test'],
            [
                'name' => 'WatchHub Admin',
                'password' => Hash::make('password'),
                'phone' => '+92 300 1111111',
                'role' => 'admin',
            ]
        );

        $customer = User::updateOrCreate(
            ['email' => 'customer@watchhub.test'],
            [
                'name' => 'Omar Customer',
                'password' => Hash::make('password'),
                'phone' => '+92 300 2222222',
                'role' => 'customer',
            ]
        );

        Address::updateOrCreate(
            ['user_id' => $customer->id, 'label' => 'Home'],
            [
                'full_name' => $customer->name,
                'phone' => $customer->phone,
                'street' => 'Main Boulevard, Gulberg',
                'city' => 'Lahore',
                'state' => 'Punjab',
                'country' => 'Pakistan',
                'postal_code' => '54000',
                'is_default' => true,
            ]
        );

        $brands = collect([
            ['name' => 'Aureon', 'slug' => 'aureon', 'country_of_origin' => 'Switzerland'],
            ['name' => 'Seiko', 'slug' => 'seiko', 'country_of_origin' => 'Japan'],
            ['name' => 'Tissot', 'slug' => 'tissot', 'country_of_origin' => 'Switzerland'],
            ['name' => 'Fossil', 'slug' => 'fossil', 'country_of_origin' => 'United States'],
        ])->mapWithKeys(fn ($brand) => [
            $brand['slug'] => Brand::updateOrCreate(['slug' => $brand['slug']], $brand + ['is_active' => true]),
        ]);

        $categories = collect([
            ['name' => 'Dress Watches', 'slug' => 'dress-watches', 'sort_order' => 1],
            ['name' => 'Sports Watches', 'slug' => 'sports-watches', 'sort_order' => 2],
            ['name' => 'Smart Casual', 'slug' => 'smart-casual', 'sort_order' => 3],
            ['name' => 'Diver Watches', 'slug' => 'diver-watches', 'sort_order' => 4],
        ])->mapWithKeys(fn ($category) => [
            $category['slug'] => Category::updateOrCreate(['slug' => $category['slug']], $category + ['is_active' => true]),
        ]);

        foreach ($this->products() as $item) {
            $product = Product::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => 'Premium craftsmanship, everyday reliability, and refined finishing for collectors and first-time buyers.',
                    'short_description' => 'A polished WatchHub pick with verified stock and secure checkout.',
                    'price' => $item['price'],
                    'sale_price' => $item['sale_price'],
                    'stock' => $item['stock'],
                    'sku' => $item['sku'],
                    'brand_id' => $brands[$item['brand']]->id,
                    'category_id' => $categories[$item['category']]->id,
                    'case_material' => 'Stainless steel',
                    'strap_material' => str_contains($item['name'], 'Leather') ? 'Leather' : 'Steel bracelet',
                    'dial_color' => $item['dial_color'],
                    'movement_type' => $item['movement_type'],
                    'water_resistance' => '50m',
                    'case_diameter' => '40mm',
                    'is_featured' => $item['featured'],
                    'is_new_arrival' => $item['new'],
                    'is_active' => true,
                    'average_rating' => 4.6,
                    'review_count' => 12,
                ]
            );

            $product->images()->updateOrCreate(
                ['sort_order' => 0],
                ['path' => $item['image'], 'alt_text' => $item['name'], 'is_primary' => true]
            );
        }

        foreach ([
            ['question' => 'How long does delivery take?', 'answer' => 'Most orders are delivered within 3 to 5 business days.', 'category' => 'shipping', 'sort_order' => 1],
            ['question' => 'Are watches covered by warranty?', 'answer' => 'Every WatchHub order includes brand warranty details and a 7-day inspection window.', 'category' => 'warranty', 'sort_order' => 2],
            ['question' => 'Can I pay cash on delivery?', 'answer' => 'Yes, COD is available alongside card and PayPal payment methods.', 'category' => 'payments', 'sort_order' => 3],
        ] as $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq + ['is_active' => true]);
        }
    }

    private function products(): array
    {
        return [
            ['name' => 'Aureon Celeste Automatic', 'slug' => 'aureon-celeste-automatic', 'brand' => 'aureon', 'category' => 'dress-watches', 'price' => 780, 'sale_price' => 699, 'stock' => 18, 'sku' => 'WH-AUR-001', 'dial_color' => 'Ivory', 'movement_type' => 'Automatic', 'image' => 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=900&q=80', 'featured' => true, 'new' => true],
            ['name' => 'Seiko Prospex Tide', 'slug' => 'seiko-prospex-tide', 'brand' => 'seiko', 'category' => 'diver-watches', 'price' => 520, 'sale_price' => null, 'stock' => 25, 'sku' => 'WH-SEI-220', 'dial_color' => 'Ocean Blue', 'movement_type' => 'Solar', 'image' => 'https://images.unsplash.com/photo-1547996160-81dfa63595aa?auto=format&fit=crop&w=900&q=80', 'featured' => true, 'new' => false],
            ['name' => 'Tissot Heritage Chrono', 'slug' => 'tissot-heritage-chrono', 'brand' => 'tissot', 'category' => 'sports-watches', 'price' => 640, 'sale_price' => 589, 'stock' => 12, 'sku' => 'WH-TIS-440', 'dial_color' => 'Black', 'movement_type' => 'Quartz Chronograph', 'image' => 'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?auto=format&fit=crop&w=900&q=80', 'featured' => true, 'new' => true],
            ['name' => 'Fossil Townsman Leather', 'slug' => 'fossil-townsman-leather', 'brand' => 'fossil', 'category' => 'smart-casual', 'price' => 210, 'sale_price' => 169, 'stock' => 40, 'sku' => 'WH-FOS-118', 'dial_color' => 'Cream', 'movement_type' => 'Quartz', 'image' => 'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=900&q=80', 'featured' => false, 'new' => true],
            ['name' => 'Aureon Nightfall Reserve', 'slug' => 'aureon-nightfall-reserve', 'brand' => 'aureon', 'category' => 'sports-watches', 'price' => 940, 'sale_price' => null, 'stock' => 7, 'sku' => 'WH-AUR-007', 'dial_color' => 'Charcoal', 'movement_type' => 'Automatic GMT', 'image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=900&q=80', 'featured' => true, 'new' => false],
            ['name' => 'Fossil Minimalist Steel', 'slug' => 'fossil-minimalist-steel', 'brand' => 'fossil', 'category' => 'dress-watches', 'price' => 185, 'sale_price' => null, 'stock' => 31, 'sku' => 'WH-FOS-209', 'dial_color' => 'Silver', 'movement_type' => 'Quartz', 'image' => 'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?auto=format&fit=crop&w=900&q=80', 'featured' => false, 'new' => true],
        ];
    }
}
