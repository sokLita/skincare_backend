<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Category, Product};
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create skincare categories
        $categories = [
            ['name' => 'Cleansers', 'slug' => 'cleansers', 'description' => 'Gentle facial cleansers for all skin types', 'image' => 'categories/cleansers.jpg'],
            ['name' => 'Moisturizers', 'slug' => 'moisturizers', 'description' => 'Hydrating moisturizers and creams', 'image' => 'categories/moisturizers.jpg'],
            ['name' => 'Serums', 'slug' => 'serums', 'description' => 'Concentrated serums for targeted treatment', 'image' => 'categories/serums.jpg'],
            ['name' => 'Sunscreen', 'slug' => 'sunscreen', 'description' => 'SPF protection for daily use', 'image' => 'categories/sunscreen.jpg'],
            ['name' => 'Masks', 'slug' => 'masks', 'description' => 'Face masks for deep cleansing and hydration', 'image' => 'categories/masks.jpg'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $cat = Category::updateOrCreate(['slug' => $category['slug']], $category);
            $categoryIds[$category['slug']] = $cat->id;
        }

        // Create skincare products
        $products = [
            // Cleansers
            ['name' => 'Gentle Foaming Cleanser', 'category_slug' => 'cleansers', 'price' => 28.00, 'old_price' => 35.00, 'stock' => 50, 'description' => 'A mild, sulfate-free foaming cleanser that removes impurities without stripping the skin of its natural moisture. Infused with green tea and chamomile.'],
            ['name' => 'Oil-Based Makeup Remover', 'category_slug' => 'cleansers', 'price' => 32.00, 'old_price' => null, 'stock' => 40, 'description' => 'Dissolves even waterproof makeup effortlessly. Formulated with jojoba oil and vitamin E to nourish while cleansing.'],
            ['name' => 'Hydrating Cream Cleanser', 'category_slug' => 'cleansers', 'price' => 26.00, 'old_price' => 32.00, 'stock' => 35, 'description' => 'A rich, creamy cleanser that hydrates while gently cleansing. Perfect for dry and sensitive skin types.'],
            ['name' => 'Exfoliating Face Wash', 'category_slug' => 'cleansers', 'price' => 30.00, 'old_price' => null, 'stock' => 45, 'description' => 'Gentle physical exfoliation with fine jojoba beads. Removes dead skin cells for a brighter, smoother complexion.'],

            // Moisturizers
            ['name' => 'Daily Hydrating Moisturizer', 'category_slug' => 'moisturizers', 'price' => 45.00, 'old_price' => 55.00, 'stock' => 60, 'description' => 'Lightweight yet deeply hydrating daily moisturizer with hyaluronic acid and ceramides. Restores the skin barrier.'],
            ['name' => 'Night Repair Cream', 'category_slug' => 'moisturizers', 'price' => 58.00, 'old_price' => 72.00, 'stock' => 30, 'description' => 'Rich overnight cream with retinol and peptides. Works while you sleep to reduce fine lines and improve elasticity.'],
            ['name' => 'Mattifying Gel Moisturizer', 'category_slug' => 'moisturizers', 'price' => 38.00, 'old_price' => null, 'stock' => 55, 'description' => 'Oil-free gel moisturizer that controls shine and minimizes pores. Ideal for oily and combination skin.'],
            ['name' => 'Barrier Repair Balm', 'category_slug' => 'moisturizers', 'price' => 42.00, 'old_price' => null, 'stock' => 25, 'description' => 'Intensive balm for compromised skin barriers. Packed with panthenol, shea butter, and omega fatty acids.'],

            // Serums
            ['name' => 'Vitamin C Brightening Serum', 'category_slug' => 'serums', 'price' => 52.00, 'old_price' => 65.00, 'stock' => 40, 'description' => 'Stabilized 15% vitamin C serum with ferulic acid and vitamin E. Brightens dark spots and evens skin tone.'],
            ['name' => 'Hyaluronic Acid Plumping Serum', 'category_slug' => 'serums', 'price' => 48.00, 'old_price' => 60.00, 'stock' => 55, 'description' => 'Triple-weight hyaluronic acid serum that delivers hydration to multiple layers of the skin for a plump, dewy look.'],
            ['name' => 'Niacinamide 10% + Zinc Serum', 'category_slug' => 'serums', 'price' => 36.00, 'old_price' => null, 'stock' => 65, 'description' => 'High-potency niacinamide serum that reduces pores, controls oil, and improves skin texture. Suitable for all skin types.'],
            ['name' => 'Retinol Anti-Aging Serum', 'category_slug' => 'serums', 'price' => 62.00, 'old_price' => 78.00, 'stock' => 35, 'description' => 'Encapsulated retinol serum for gradual release. Reduces fine lines, wrinkles, and uneven texture with minimal irritation.'],

            // Sunscreen
            ['name' => 'Mineral SPF 50 Sunscreen', 'category_slug' => 'sunscreen', 'price' => 34.00, 'old_price' => 42.00, 'stock' => 45, 'description' => 'Zinc oxide-based mineral sunscreen with a subtle tint. Provides broad-spectrum protection without white cast.'],
            ['name' => 'Lightweight Daily SPF 30', 'category_slug' => 'sunscreen', 'price' => 28.00, 'old_price' => null, 'stock' => 70, 'description' => 'Feather-light chemical sunscreen for daily wear. Absorbs quickly with zero greasy residue. Perfect under makeup.'],
            ['name' => 'Water Resistant SPF 50+', 'category_slug' => 'sunscreen', 'price' => 38.00, 'old_price' => null, 'stock' => 40, 'description' => 'Water-resistant sunscreen for active lifestyles. Provides 80 minutes of protection while swimming or sweating.'],

            // Masks
            ['name' => 'Clay Detox Face Mask', 'category_slug' => 'masks', 'price' => 32.00, 'old_price' => 40.00, 'stock' => 50, 'description' => 'Purifying kaolin clay mask with charcoal and tea tree oil. Draws out impurities and excess oil for clearer skin.'],
            ['name' => 'Hydrating Sheet Mask (5 Pack)', 'category_slug' => 'masks', 'price' => 22.00, 'old_price' => null, 'stock' => 80, 'description' => 'Set of 5 hydrogel sheet masks soaked in hyaluronic acid and aloe vera. Instant hydration boost for dull skin.'],
            ['name' => 'Brightening Turmeric Mask', 'category_slug' => 'masks', 'price' => 36.00, 'old_price' => 45.00, 'stock' => 30, 'description' => 'Turmeric and yogurt-based mask that brightens and evens skin tone. Reduces hyperpigmentation with regular use.'],
            ['name' => 'Overnight Hydra-Gel Mask', 'category_slug' => 'masks', 'price' => 42.00, 'old_price' => null, 'stock' => 35, 'description' => 'Leave-on gel mask that provides intense hydration overnight. Wake up to plump, glowing skin.'],
        ];

        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'category_id' => $categoryIds[$product['category_slug']],
                'price' => $product['price'],
                'old_price' => $product['old_price'],
                'stock' => $product['stock'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }
    }
}
