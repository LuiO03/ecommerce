<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'moda', 'ropa', 'tendencias', 'outfits', 'estilo',
            'camisetas', 'jeans', 'vestidos', 'sudaderas', 'chaquetas',
            'ropa-hombre', 'ropa-mujer', 'ropa-unisex',
            'streetwear', 'casual', 'elegante',
            'verano', 'invierno',
            'ofertas', 'descuentos', 'nuevos-ingresos',
        ];

        foreach ($tags as $name) {
            Tag::create([
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }
}
