<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $categories = ['Electronics', 'Clothing', 'Books', 'Home & Kitchen', 'Sports'];
        
        return [
            'name' => $this->faker->productName(),
            'description' => $this->faker->paragraph(),
            'category' => Arr::random($categories),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'photos' => json_encode([$this->faker->imageUrl(640, 480, 'product')]),
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
        ]);
    }
}
