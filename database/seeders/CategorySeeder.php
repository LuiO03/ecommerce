<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // obtener las familias ya insertadas
        $families = DB::table('families')->pluck('id', 'slug');

        $categories = [
            // Categorías de Ropa Hombre
            [
                'name' => 'Camisas',
                'slug' => Str::slug('Camisas'),
                'description' => 'Camisas casuales y formales para hombre.',
                'family_id' => $families['ropa-hombre'] ?? 1,
                'image' => 'categories/camisas.jpg',
                'status' => true,
            ],
            [
                'name' => 'Pantalones',
                'slug' => Str::slug('Pantalones Hombre'),
                'description' => 'Jeans, joggers y pantalones de vestir.',
                'family_id' => $families['ropa-hombre'] ?? 1,
                'image' => 'categories/pantalones-hombre.jpg',
                'status' => true,
            ],

            // Categorías de Ropa Mujer
            [
                'name' => 'Vestidos',
                'slug' => Str::slug('Vestidos'),
                'description' => 'Vestidos elegantes, casuales y de fiesta.',
                'family_id' => $families['ropa-mujer'] ?? 2,
                'image' => 'categories/vestidos.jpg',
                'status' => true,
            ],
            [
                'name' => 'Blusas',
                'slug' => Str::slug('Blusas'),
                'description' => 'Blusas modernas para toda ocasión.',
                'family_id' => $families['ropa-mujer'] ?? 2,
                'image' => 'categories/blusas.jpg',
                'status' => true,
            ],

            // Categorías de Niños
            [
                'name' => 'Ropa Infantil',
                'slug' => Str::slug('Ropa Infantil'),
                'description' => 'Prendas cómodas para niños y niñas.',
                'family_id' => $families['ninos'] ?? 3,
                'image' => 'categories/ropa-infantil.jpg',
                'status' => true,
            ],

            // Categorías de Accesorios
            [
                'name' => 'Gorras',
                'slug' => Str::slug('Gorras'),
                'description' => 'Gorras deportivas y urbanas.',
                'family_id' => $families['accesorios'] ?? 4,
                'image' => 'categories/gorras.jpg',
                'status' => true,
            ],
            [
                'name' => 'Bolsos',
                'slug' => Str::slug('Bolsos'),
                'description' => 'Bolsos para mujer y mochilas unisex.',
                'family_id' => $families['accesorios'] ?? 4,
                'image' => 'categories/bolsos.jpg',
                'status' => true,
            ],
        ];

        DB::table('categories')->insert($categories);
    }
}
