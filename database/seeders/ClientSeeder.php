<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Crear clientes de ejemplo (usuarios con rol Cliente)
        // Nota: el rol "Cliente" se crea en RolePermissionSeeder.

        $clients = User::factory(12)->create()->each(function (User $user, int $index) {
            $user->slug = Str::slug(($user->name ?? 'cliente') . '-' . uniqid());
            $user->status = true;

            // Alternar verificación de email para poder probar el filtro.
            $user->email_verified_at = $index % 2 === 0 ? now() : null;

            $user->save();

            if (!$user->hasRole('Cliente')) {
                $user->assignRole('Cliente');
            }
        });

        // Evitar warnings de variable no usada en algunos linters
        unset($clients);
    }
}
