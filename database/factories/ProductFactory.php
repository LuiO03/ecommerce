<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->bothify('SKU-#####'),
            'name' => $this->faker->words(3, true),
            'slug' => Str::slug(Str::limit($this->faker->words(3, true), 50, '')),
            'description' => $this->faker->text(),
            'price' => $this->faker->randomFloat(2, 5, 500),
            'discount' => $this->faker->optional()->randomFloat(2, 1, 100),
            'status' => $this->faker->boolean(80), // 80% de probabilidad de ser true
            'category_id' => $this->faker->numberBetween(1, 10), // Asumiendo que hay al menos 10 categorías
        ];
    }
}
