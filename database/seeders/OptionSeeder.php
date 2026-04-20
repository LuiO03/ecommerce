<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            [
                'name' => 'Talla',
                'features' => [
                    ['value' => '36', 'description' => 'Talla 36'],
                    ['value' => '38', 'description' => 'Talla 38'],
                    ['value' => '40', 'description' => 'Talla 40'],
                    ['value' => '42', 'description' => 'Talla 42'],
                    ['value' => '44', 'description' => 'Talla 44'],
                    ['value' => '46', 'description' => 'Talla 46'],
                    ['value' => '48', 'description' => 'Talla 48'],
                ]
            ],
            [
                'name' => 'Color',
                'features' => [
                    // Básicos
                    ['value' => 'Negro', 'description' => '#1C1C1C'],
                    ['value' => 'Blanco', 'description' => '#F5F5F5'],
                    ['value' => 'Gris claro', 'description' => '#D1D5DB'],
                    ['value' => 'Gris oscuro', 'description' => '#4B5563'],

                    // Tierra / neutros
                    ['value' => 'Beige', 'description' => '#E5D3B3'],
                    ['value' => 'Arena', 'description' => '#D6C6A8'],
                    ['value' => 'Camel', 'description' => '#C19A6B'],
                    ['value' => 'Marrón', 'description' => '#8B5E3C'],
                    ['value' => 'Chocolate', 'description' => '#5C4033'],

                    // Azules
                    ['value' => 'Azul marino', 'description' => '#1E3A5F'],
                    ['value' => 'Azul denim', 'description' => '#3B5998'],
                    ['value' => 'Azul cielo', 'description' => '#7EC8E3'],

                    // Rojos / rosas
                    ['value' => 'Rojo vino', 'description' => '#7B1E3A'],
                    ['value' => 'Rojo terracota', 'description' => '#A63A3A'],
                    ['value' => 'Rosa palo', 'description' => '#D8A7B1'],
                    ['value' => 'Rosa pastel', 'description' => '#F4C2C2'],

                    // Verdes
                    ['value' => 'Verde oliva', 'description' => '#6B8E23'],
                    ['value' => 'Verde militar', 'description' => '#4B5320'],
                    ['value' => 'Verde esmeralda', 'description' => '#2E8B57'],

                    // Amarillos / cálidos
                    ['value' => 'Mostaza', 'description' => '#D4A017'],
                    ['value' => 'Amarillo pastel', 'description' => '#FCE883'],
                    ['value' => 'Naranja quemado', 'description' => '#CC5500'],

                    // Otros modernos
                    ['value' => 'Lavanda', 'description' => '#C8A2C8'],
                    ['value' => 'Lila', 'description' => '#B57EDC'],
                    ['value' => 'Turquesa', 'description' => '#40E0D0'],
                ]
            ],
        ];

        foreach ($options as $option) {
            $name = trim($option['name']);
            $slug = Option::generateUniqueSlug($name);

            $optionModel = Option::create([
                'name' => $name,
                'slug' => $slug,
            ]);

            foreach ($option['features'] as $feature) {
                $optionModel->features()->create([
                    'value' => $feature['value'],
                    'description' => $feature['description'],
                ]);
            }
        }
    }
}
