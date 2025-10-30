<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Datos que quieres crear
        $data = [
            'name'  => 'Luis Osorio',
            'email' => '70098517@institutocajas.info',
        ];

        // Crear o actualizar (evita duplicados)
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name'  => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('luis988434679kira'),
                'slug' => Str::slug($data['name'] . '-' . uniqid()),
            ]
        );

        // Asignar rol de administrador
        $user->assignRole('superadministrador');
    }
}
