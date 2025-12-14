<?php

namespace Database\Factories;
use Illuminate\Support\Str;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Family>
 */
class FamilyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true); // Ejemplo: "Moda Urbana"
        return [
            'name' => ucfirst($name),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->sentence(8),
            'image' => 'families/default.jpg',
            'status' => $this->faker->boolean(80), // 80% activos
        ];
    }
}
