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
        // Datos del superadministrador
        $data = [
            'name'      => 'Luis',
            'last_name' => 'Osorio',
            'email'     => '70098517@institutocajas.info',
        ];

        // Crear o actualizar (evita duplicados)
        $user = User::updateOrCreate(
            ['email' => $data['email']],
            [
                'name'      => $data['name'],
                'last_name' => $data['last_name'],
                'email'     => $data['email'],
                'password'  => Hash::make('luis988434679kira'),
                'slug'      => Str::slug($data['name'] . ' ' . $data['last_name'] . '-' . uniqid()),
                'status'    => true,
            ]
        );

        // Asignar rol de superadministrador
        $user->assignRole('superadministrador');
    }
}
