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
                'features' => [
                    [
                        'value' => '#FF0000',
                        'description' => 'Rojo'
                    ],
                    [
                        'value' => '#00FF00',
                        'description' => 'Verde'
                    ],
                    [
                        'value' => '#0000FF',
                        'description' => 'Azul'
                    ],
                    [
                        'value' => '#FFFF00',
                        'description' => 'Amarillo'
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
