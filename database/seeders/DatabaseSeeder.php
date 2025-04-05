<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only seed if no users exist
        if (User::count() == 0) {
            // Create a default admin user
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@laravelshop.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);

            // Create multiple test users
            for ($i = 0; $i < 5; $i++) {
                User::create([
                    'name' => 'Test User ' . ($i + 1),
                    'email' => 'test' . ($i + 1) . '@example.com',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ]);
            }
        }

        // Only seed if no products exist
        if (Product::count() == 0) {
            // Create multiple products
            $categories = ['Electronics', 'Clothing', 'Books', 'Home & Kitchen', 'Sports'];
            
            for ($i = 0; $i < 20; $i++) {
                Product::create([
                    'name' => 'Product ' . ($i + 1),
                    'description' => 'Description for product ' . ($i + 1),
                    'category' => $categories[array_rand($categories)],
                    'price' => rand(10, 500) / 1,
                    'photos' => json_encode(['/images/default-product.jpg']),
                    'is_deleted' => false,
                ]);
            }

            // Create some deleted products
            for ($i = 0; $i < 5; $i++) {
                Product::create([
                    'name' => 'Deleted Product ' . ($i + 1),
                    'description' => 'Description for deleted product ' . ($i + 1),
                    'category' => $categories[array_rand($categories)],
                    'price' => rand(10, 500) / 1,
                    'photos' => json_encode(['/images/default-product.jpg']),
                    'is_deleted' => true,
                ]);
            }
        }
    }
}
