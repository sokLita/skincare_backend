<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Category, Product};
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and literature'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home improvement and garden supplies'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and accessories'],
        ];

        // Get or create categories and store their IDs
        $categoryIds = [];
        foreach ($categories as $category) {
            $cat = Category::updateOrCreate(['slug' => $category['slug']], $category);
            $categoryIds[$category['slug']] = $cat->id;
        }

        // Create products using actual category IDs
        $products = [
            ['name' => 'Wireless Headphones', 'category_slug' => 'electronics', 'price' => 79.99, 'stock' => 50, 'description' => 'High-quality wireless headphones with noise cancellation'],
            ['name' => 'Smartphone', 'category_slug' => 'electronics', 'price' => 699.99, 'stock' => 30, 'description' => 'Latest smartphone with advanced features'],
            ['name' => 'Laptop', 'category_slug' => 'electronics', 'price' => 1299.99, 'stock' => 20, 'description' => 'Powerful laptop for work and gaming'],
            ['name' => 'T-Shirt', 'category_slug' => 'clothing', 'price' => 19.99, 'stock' => 100, 'description' => 'Comfortable cotton t-shirt'],
            ['name' => 'Jeans', 'category_slug' => 'clothing', 'price' => 49.99, 'stock' => 75, 'description' => 'Classic fit denim jeans'],
            ['name' => 'Novel Book', 'category_slug' => 'books', 'price' => 14.99, 'stock' => 200, 'description' => 'Bestselling fiction novel'],
            ['name' => 'Cookbook', 'category_slug' => 'books', 'price' => 24.99, 'stock' => 60, 'description' => 'Delicious recipes for home cooking'],
            ['name' => 'Garden Tools Set', 'category_slug' => 'home-garden', 'price' => 39.99, 'stock' => 40, 'description' => 'Complete set of garden tools'],
            ['name' => 'Basketball', 'category_slug' => 'sports', 'price' => 29.99, 'stock' => 80, 'description' => 'Official size basketball'],
            ['name' => 'Yoga Mat', 'category_slug' => 'sports', 'price' => 19.99, 'stock' => 120, 'description' => 'Non-slip yoga mat for exercise'],
        ];

        foreach ($products as $product) {
            Product::create([
                'name' => $product['name'],
                'slug' => Str::slug($product['name']),
                'category_id' => $categoryIds[$product['category_slug']],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'description' => $product['description'],
                'is_active' => true,
            ]);
        }
    }
}
