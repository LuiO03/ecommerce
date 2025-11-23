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
        // Definición profesional con descripción
        $permissions = [
            // Productos
            ['name' => 'ver productos', 'description' => 'Puede ver la lista de productos'],
            ['name' => 'crear productos', 'description' => 'Puede crear nuevos productos'],
            ['name' => 'editar productos', 'description' => 'Puede editar productos existentes'],
            ['name' => 'eliminar productos', 'description' => 'Puede eliminar productos del sistema'],

            // Categorías
            ['name' => 'ver categorías', 'description' => 'Puede ver la lista de categorías'],
            ['name' => 'crear categorías', 'description' => 'Puede crear nuevas categorías'],
            ['name' => 'editar categorías', 'description' => 'Puede editar categorías existentes'],
            ['name' => 'eliminar categorías', 'description' => 'Puede eliminar categorías del sistema'],

            // Usuarios
            ['name' => 'ver usuarios', 'description' => 'Puede ver la lista de usuarios'],
            ['name' => 'crear usuarios', 'description' => 'Puede crear nuevos usuarios'],
            ['name' => 'editar usuarios', 'description' => 'Puede editar usuarios existentes'],
            ['name' => 'eliminar usuarios', 'description' => 'Puede eliminar usuarios del sistema'],

            // Configuración
            ['name' => 'acceder configuración', 'description' => 'Puede acceder a la configuración global del sistema'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['name' => $perm['name'], 'guard_name' => 'web'],
                ['description' => $perm['description']]
            );
        }

        // === ROLES ===
        $superadmin = Role::firstOrCreate([
            'name' => 'Superadministrador',
            'description' => 'Acceso total al sistema, incluyendo configuración global y gestión avanzada.'
        ]);
        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'description' => 'Gestión principal de productos, categorías y usuarios, sin acceso a configuración global.'
        ]);
        $vendedor = Role::firstOrCreate([
            'name' => 'Vendedor',
            'description' => 'Acceso limitado a productos para tareas de venta y edición básica.'
        ]);
        $supervisor = Role::firstOrCreate([
            'name' => 'Supervisor',
            'description' => 'Supervisa operaciones y puede ver reportes y movimientos, sin modificar configuración.'
        ]);
        $almacenero = Role::firstOrCreate([
            'name' => 'Almacenero',
            'description' => 'Gestiona inventario y movimientos de productos en almacén.'
        ]);

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

        // Supervisor: solo ver productos, categorías y usuarios
        $supervisor->syncPermissions([
            'ver productos',
            'ver categorías',
            'ver usuarios',
        ]);

        // Almacenero: ver y editar productos
        $almacenero->syncPermissions([
            'ver productos',
            'editar productos',
        ]);
    }
}
