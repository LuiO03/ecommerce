<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear 5 usuarios de prueba con datos aleatorios y slug
        User::factory(5)->create()->each(function($user){
            $user->slug = Str::slug($user->name . '-' . uniqid());
            $user->save();
        });

        // Crear usuario de prueba específico
        User::factory()->create([
            'name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'slug' => Str::slug('Test User-' . uniqid()),
            'status' => true,
        ]);


        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            FamilySeeder::class,
            CategorySeeder::class,
            TagSeeder::class,
            PostSeeder::class,
            CompanySettingSeeder::class,
        ]);
    }

}
