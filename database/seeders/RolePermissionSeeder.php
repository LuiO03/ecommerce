<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // === PERMISOS DEFINIDOS POR MÓDULO (CRUD + ESPECIALES) ===
        $permissions = [

            // --- Productos ---
            'productos' => [
                ['name' => 'ver productos', 'description' => 'Puede ver la lista de productos'],
                ['name' => 'crear productos', 'description' => 'Puede crear nuevos productos'],
                ['name' => 'editar productos', 'description' => 'Puede editar productos existentes'],
                ['name' => 'eliminar productos', 'description' => 'Puede eliminar productos del sistema'],
                ['name' => 'ajustar stock', 'description' => 'Puede modificar existencias en inventario'],
            ],

            // --- Categorías ---
            'categorías' => [
                ['name' => 'ver categorías', 'description' => 'Puede ver la lista de categorías'],
                ['name' => 'crear categorías', 'description' => 'Puede crear nuevas categorías'],
                ['name' => 'editar categorías', 'description' => 'Puede editar categorías existentes'],
                ['name' => 'eliminar categorías', 'description' => 'Puede eliminar categorías del sistema'],
            ],

            // --- Usuarios ---
            'usuarios' => [
                ['name' => 'ver usuarios', 'description' => 'Puede ver la lista de usuarios'],
                ['name' => 'crear usuarios', 'description' => 'Puede crear nuevos usuarios'],
                ['name' => 'editar usuarios', 'description' => 'Puede editar usuarios existentes'],
                ['name' => 'eliminar usuarios', 'description' => 'Puede eliminar usuarios del sistema'],
                ['name' => 'resetear contraseña de usuario', 'description' => 'Puede resetear contraseña de un usuario'],
            ],

            // --- Configuración (no CRUD) ---
            'configuración' => [
                ['name' => 'acceder configuración', 'description' => 'Puede acceder a la configuración global del sistema'],
                ['name' => 'editar configuración', 'description' => 'Puede modificar parámetros avanzados del sistema'],
            ],

            // --- Reportes ---
            'reportes' => [
                ['name' => 'ver reportes', 'description' => 'Puede ver reportes del sistema'],
                ['name' => 'exportar reportes', 'description' => 'Puede exportar reportes en PDF o Excel'],
            ],
        ];

        // === CREAR PERMISOS ===
        foreach ($permissions as $module => $items) {
            foreach ($items as $perm) {
                Permission::firstOrCreate(
                    ['name' => $perm['name'], 'guard_name' => 'web'],
                    [
                        'module' => $module,
                        'description' => $perm['description'] ?? null,
                    ]
                );
            }
        }

        // === CREAR ROLES ===
        $superadmin = Role::firstOrCreate(
            ['name' => 'Superadministrador'],
            [
                'guard_name' => 'web',
                'description' => 'Control total del sistema y acceso a todas las funciones.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            [
                'guard_name' => 'web',
                'description' => 'Gestión completa del sistema excepto configuración avanzada.',
            ]
        );

        $vendedor = Role::firstOrCreate(
            ['name' => 'Vendedor'],
            [
                'guard_name' => 'web',
                'description' => 'Gestión de ventas y productos con permisos limitados.',
            ]
        );

        $supervisor = Role::firstOrCreate(
            ['name' => 'Supervisor'],
            [
                'guard_name' => 'web',
                'description' => 'Supervisor general con acceso principalmente de lectura.',
            ]
        );

        $almacenero = Role::firstOrCreate(
            ['name' => 'Almacenero'],
            [
                'guard_name' => 'web',
                'description' => 'Gestión del inventario y existencias.',
            ]
        );

        // === ASIGNACIÓN DE PERMISOS A ROLES ===

        // Superadministrador → TODO
        $superadmin->syncPermissions(Permission::all());

        // Administrador → CRUD completo pero sin configuración avanzada
        $admin->syncPermissions([
            // Productos
            'ver productos', 'crear productos', 'editar productos', 'eliminar productos',

            // Categorías
            'ver categorías', 'crear categorías', 'editar categorías', 'eliminar categorías',

            // Usuarios
            'ver usuarios', 'crear usuarios', 'editar usuarios', 'eliminar usuarios',
            'resetear contraseña de usuario',

            // Reportes
            'ver reportes', 'exportar reportes',
        ]);

        // Vendedor → Acceso limitado
        $vendedor->syncPermissions([
            'ver productos',
            'crear productos',
            'editar productos',

            'ver categorías',

            'ver usuarios',
        ]);

        // Supervisor → Solo lectura del sistema
        $supervisor->syncPermissions([
            'ver productos',
            'ver categorías',
            'ver usuarios',
            'ver reportes',
        ]);

        // Almacenero → Inventario + lectura básica
        $almacenero->syncPermissions([
            'ver productos',
            'editar productos',
            'ajustar stock',
        ]);
    }
}
