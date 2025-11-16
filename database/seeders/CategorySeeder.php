<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Obtener familias
        $families = DB::table('families')->pluck('id', 'slug');

        /* ============================================
         * 1. Insertar categorías principales
         * ============================================ */
        $parentCategories = [
            // Ropa Hombre
            [
                'name' => 'Camisas',
                'slug' => Str::slug('Camisas'),
                'description' => 'Camisas casuales y formales para hombre.',
                'family_id' => $families['ropa-hombre'] ?? 1,
                'image' => 'categories/camisas.jpg',
                'status' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Pantalones Hombre',
                'slug' => Str::slug('Pantalones Hombre'),
                'description' => 'Jeans, joggers y pantalones de vestir.',
                'family_id' => $families['ropa-hombre'] ?? 1,
                'image' => 'categories/pantalones-hombre.jpg',
                'status' => true,
                'parent_id' => null,
            ],

            // Ropa Mujer
            [
                'name' => 'Vestidos',
                'slug' => Str::slug('Vestidos'),
                'description' => 'Vestidos elegantes, casuales y de fiesta.',
                'family_id' => $families['ropa-mujer'] ?? 2,
                'image' => 'categories/vestidos.jpg',
                'status' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Blusas',
                'slug' => Str::slug('Blusas'),
                'description' => 'Blusas modernas para toda ocasión.',
                'family_id' => $families['ropa-mujer'] ?? 2,
                'image' => 'categories/blusas.jpg',
                'status' => true,
                'parent_id' => null,
            ],

            // Niños
            [
                'name' => 'Ropa Infantil',
                'slug' => Str::slug('Ropa Infantil'),
                'description' => 'Prendas cómodas para niños y niñas.',
                'family_id' => $families['ninos'] ?? 3,
                'image' => 'categories/ropa-infantil.jpg',
                'status' => true,
                'parent_id' => null,
            ],

            // Accesorios
            [
                'name' => 'Gorras',
                'slug' => Str::slug('Gorras'),
                'description' => 'Gorras deportivas y urbanas.',
                'family_id' => $families['accesorios'] ?? 4,
                'image' => 'categories/gorras.jpg',
                'status' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Bolsos',
                'slug' => Str::slug('Bolsos'),
                'description' => 'Bolsos para mujer y mochilas unisex.',
                'family_id' => $families['accesorios'] ?? 4,
                'image' => 'categories/bolsos.jpg',
                'status' => true,
                'parent_id' => null,
            ],
        ];

        DB::table('categories')->insert($parentCategories);

        /* =======================================================
         * Obtener IDs de las categorías principales recién creadas
         * ======================================================= */
        $parents = DB::table('categories')->pluck('id', 'slug');

        /* ============================================
         * 2. Insertar subcategorías (categorías hijas)
         * ============================================ */
        $childCategories = [
            // Hijos de Camisas
            [
                'name' => 'Camisas Manga Larga',
                'slug' => Str::slug('Camisas Manga Larga'),
                'description' => 'Camisas formales y casuales de manga larga.',
                'family_id' => null,
                'image' => 'categories/camisas-manga-larga.jpg',
                'parent_id' => $parents['camisas'] ?? null,
                'status' => true,
            ],
            [
                'name' => 'Camisas Manga Corta',
                'slug' => Str::slug('Camisas Manga Corta'),
                'description' => 'Camisas frescas y ligeras.',
                'family_id' => null,
                'image' => 'categories/camisas-manga-corta.jpg',
                'parent_id' => $parents['camisas'] ?? null,
                'status' => true,
            ],

            // Hijos de Pantalones Hombre
            [
                'name' => 'Jeans',
                'slug' => Str::slug('Jeans Hombre'),
                'description' => 'Jeans clásicos y modernos.',
                'family_id' => null,
                'image' => 'categories/jeans-hombre.jpg',
                'parent_id' => $parents['pantalones-hombre'] ?? null,
                'status' => true,
            ],
            [
                'name' => 'Joggers',
                'slug' => Str::slug('Joggers Hombre'),
                'description' => 'Joggers deportivos y urbanos.',
                'family_id' => null,
                'image' => 'categories/joggers-hombre.jpg',
                'parent_id' => $parents['pantalones-hombre'] ?? null,
                'status' => true,
            ],

            // Hijos de Vestidos
            [
                'name' => 'Vestidos Elegantes',
                'slug' => Str::slug('Vestidos Elegantes'),
                'description' => 'Vestidos para ocasiones especiales.',
                'family_id' => null,
                'image' => 'categories/vestidos-elegantes.jpg',
                'parent_id' => $parents['vestidos'] ?? null,
                'status' => true,
            ],
            [
                'name' => 'Vestidos Casuales',
                'slug' => Str::slug('Vestidos Casuales'),
                'description' => 'Vestidos cómodos y casuales.',
                'family_id' => null,
                'image' => 'categories/vestidos-casuales.jpg',
                'parent_id' => $parents['vestidos'] ?? null,
                'status' => true,
            ],

            // Hijos de Blusas
            [
                'name' => 'Blusas Elegantes',
                'slug' => Str::slug('Blusas Elegantes'),
                'description' => 'Blusas formales modernas.',
                'family_id' => null,
                'image' => 'categories/blusas-elegantes.jpg',
                'parent_id' => $parents['blusas'] ?? null,
                'status' => true,
            ],
            [
                'name' => 'Blusas Casual',
                'slug' => Str::slug('Blusas Casual'),
                'description' => 'Blusas para uso diario.',
                'family_id' => null,
                'image' => 'categories/blusas-casual.jpg',
                'parent_id' => $parents['blusas'] ?? null,
                'status' => true,
            ],
        ];

        DB::table('categories')->insert($childCategories);
    }
}
