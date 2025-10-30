<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // === PERMISOS ===
        // Puedes agruparlos lógicamente
        $permissions = [
            // Productos
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',

            // Categorías
            'ver categorías',
            'crear categorías',
            'editar categorías',
            'eliminar categorías',

            // Usuarios
            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',

            // Configuración
            'acceder configuración',
        ];

        foreach ($permissions as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // === ROLES ===
        $superadmin = Role::firstOrCreate(['name' => 'superadministrador']);
        $admin = Role::firstOrCreate(['name' => 'administrador']);
        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);

        // === ASIGNAR PERMISOS A ROLES ===
        // Superadministrador: todos los permisos
        $superadmin->syncPermissions(Permission::all());

        // Administrador: gestión principal, sin configuración global
        $admin->syncPermissions([
            'ver productos',
            'crear productos',
            'editar productos',
            'eliminar productos',

            'ver categorías',
            'crear categorías',
            'editar categorías',
            'eliminar categorías',

            'ver usuarios',
            'crear usuarios',
            'editar usuarios',
            'eliminar usuarios',
        ]);

        // Vendedor: acceso limitado
        $vendedor->syncPermissions([
            'ver productos',
            'crear productos',
            'editar productos',
        ]);
    }
}
