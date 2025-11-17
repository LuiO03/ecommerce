<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Family;

class FamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('families')->delete();
        $families = [
            [
                'name' => 'Ropa Hombre',
                'slug' => Str::slug('Ropa Hombre'),
                'description' => 'Prendas y accesorios para caballeros.',
                'image' => 'families/ropa-hombre.jpg',
                'status' => true,
            ],
            [
                'name' => 'Ropa Mujer',
                'slug' => Str::slug('Ropa Mujer'),
                'description' => 'Colección de moda femenina.',
                'image' => 'families/ropa-mujer.jpg',
                'status' => true,
            ],
            [
                'name' => 'Niños',
                'slug' => Str::slug('Niños'),
                'description' => 'Ropa y accesorios para niños.',
                'image' => 'families/ninos.jpg',
                'status' => true,
            ],
            [
                'name' => 'Accesorios',
                'slug' => Str::slug('Accesorios'),
                'description' => 'Bolsos, cinturones, gorras y más.',
                'image' => 'families/accesorios.jpg',
                'status' => true,
            ],
        ];

        DB::table('families')->insert($families);

        // Genera 30 familias aleatorias adicionales
        //Family::factory()->count(30)->create();
    }
}
