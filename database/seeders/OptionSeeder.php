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
                'type' => 1,
                'features' => [
                    [
                        'value' => 'S',
                        'description' => 'Talla pequeña'
                    ]
                    ,
                    [
                        'value' => 'M',
                        'description' => 'Talla mediana'
                    ],
                    [
                        'value' => 'L',
                        'description' => 'Talla grande'
                    ],
                    [
                        'value' => 'XL',
                        'description' => 'Talla extra grande'
                    ],
                ]
            ],
            [
                'name' => 'Color',
                'type' => 2,
                'features' => [
                    [
                        'value' => '#FF0000',
                        'description' => 'black'
                    ],
                    [
                        'value' => '#00FF00',
                        'description' => 'green'
                    ],
                    [
                        'value' => '#0000FF',
                        'description' => 'blue'
                    ],
                    [
                        'value' => '#FFFF00',
                        'description' => 'yellow'
                    ],
                ]
            ],
            [
                'name' => 'Sexo',
                'type' => 3,
                'features' => [
                    [
                        'value' => 'Masculino',
                        'description' => 'Ropa para hombres'
                    ],
                    [
                        'value' => 'Femenino',
                        'description' => 'Ropa para mujeres'
                    ],
                    [
                        'value' => 'Unisex',
                        'description' => 'Ropa para todos'
                    ],
                ]
            ],
        ];

        foreach ($options as $option) {
            $name = trim($option['name']);
            $slug = Option::generateUniqueSlug($name);

            $optionModel = Option::create([
                'name' => $name,
                'slug' => $slug,
                'type' => $option['type'],
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
