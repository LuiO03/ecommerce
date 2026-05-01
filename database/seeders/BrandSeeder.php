<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Nike', 'description' => 'Ropa y calzado deportivo.'],
            ['name' => 'Adidas', 'description' => 'Moda deportiva y urbana.'],
            ['name' => 'Puma', 'description' => 'Deporte y estilo de vida.'],
            ['name' => 'Reebok', 'description' => 'Calzado y ropa deportiva.'],
            ['name' => 'Levi\'s', 'description' => 'Jeans y moda casual.'],
            ['name' => 'Zara', 'description' => 'Moda contemporánea.'],
            ['name' => 'H&M', 'description' => 'Moda accesible para todos.'],
            ['name' => 'Under Armour', 'description' => 'Rendimiento deportivo.'],
            ['name' => 'New Balance', 'description' => 'Calzado y ropa deportiva.'],
            ['name' => 'Converse', 'description' => 'Calzado urbano clásico.'],
        ];

        foreach ($brands as $brand) {
            DB::table('brands')->updateOrInsert(
                ['slug' => Str::slug($brand['name'])],
                [
                    'name' => $brand['name'],
                    'slug' => Str::slug($brand['name']),
                    'description' => $brand['description'],
                    'image' => null,
                    'status' => true,
                    'created_by' => null,
                    'updated_by' => null,
                    'deleted_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
