<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();
        $tagIds = Tag::pluck('id')->toArray();
        $userId = 1; // Ajusta al id de un usuario válido en tu tabla users

        for ($i = 1; $i <= 20; $i++) {
            $title = ucfirst($faker->words(rand(2, 4), true));
            $post = Post::create([
                'title'          => $title,
                'slug'           => Str::slug($title),
                'content'        => $faker->paragraphs(5, true),
                'status'         => $faker->randomElement(['draft','pending','published','rejected']),
                'visibility'     => $faker->randomElement(['public','private','registered']),
                'allow_comments' => $faker->boolean(80),
                'views'          => $faker->numberBetween(0, 500),
                'published_at'   => $faker->optional()->dateTimeBetween('-1 year', 'now'),
                'created_by'     => $userId,
                'updated_by'     => $userId,
            ]);

            // Asociar tags aleatorios
            $postTags = $faker->randomElements($tagIds, rand(1, 4));
            $post->tags()->sync($postTags);

            // Imagen destacada
            PostImage::create([
                'post_id'     => $post->id,
                'path'        => 'posts/' . Str::slug($title) . '-main.jpg',
                'alt'         => $title,
                'description' => $faker->sentence(),
                'is_main'     => true,
                'order'       => 0,
            ]);

            // Agregar imágenes múltiples
            for ($j = 1; $j <= rand(1, 3); $j++) {
                PostImage::create([
                    'post_id'     => $post->id,
                    'path'        => 'posts/' . Str::slug($title) . '-' . $j . '.jpg',
                    'alt'         => $title,
                    'description' => $faker->sentence(),
                    'is_main'     => false,
                    'order'       => $j,
                ]);
            }
        }
    }
}

