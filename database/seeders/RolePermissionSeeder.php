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
            'Productos' => [
                ['name' => 'Ver productos', 'description' => 'Puede ver la lista de productos'],
                ['name' => 'Crear productos', 'description' => 'Puede crear nuevos productos'],
                ['name' => 'Editar productos', 'description' => 'Puede editar productos existentes'],
                ['name' => 'Eliminar productos', 'description' => 'Puede eliminar productos del sistema'],
                ['name' => 'Ajustar stock', 'description' => 'Puede modificar existencias en inventario'],
            ],

            // --- Categorías ---
            'Categorías' => [
                ['name' => 'Ver categorías', 'description' => 'Puede ver la lista de categorías'],
                ['name' => 'Crear categorías', 'description' => 'Puede crear nuevas categorías'],
                ['name' => 'Editar categorías', 'description' => 'Puede editar categorías existentes'],
                ['name' => 'Eliminar categorías', 'description' => 'Puede eliminar categorías del sistema'],
                // Permisos para jerarquía de categorías
                ['name' => 'Gestionar jerarquía de categorías', 'description' => 'Puede modificar el orden y la jerarquía de las categorías'],
                ['name' => 'Ver jerarquía de categorías', 'description' => 'Puede visualizar la estructura jerárquica de las categorías'],
            ],

            // --- Roles ---
            'Roles' => [
                ['name' => 'Ver roles', 'description' => 'Puede ver la lista de roles'],
                ['name' => 'Crear roles', 'description' => 'Puede crear nuevos roles'],
                ['name' => 'Editar roles', 'description' => 'Puede editar roles existentes'],
                ['name' => 'Eliminar roles', 'description' => 'Puede eliminar roles del sistema'],
                ['name' => 'Asignar permisos a roles', 'description' => 'Puede asignar o quitar permisos a los roles'],
            ],

            // --- Permisos ---
            'Permisos' => [
                ['name' => 'Ver permisos', 'description' => 'Puede ver la lista de permisos'],
                ['name' => 'Crear permisos', 'description' => 'Puede crear nuevos permisos'],
                ['name' => 'Editar permisos', 'description' => 'Puede editar permisos existentes'],
                ['name' => 'Eliminar permisos', 'description' => 'Puede eliminar permisos del sistema'],
            ],

            // --- Usuarios ---
            'Usuarios' => [
                ['name' => 'Ver usuarios', 'description' => 'Puede ver la lista de usuarios'],
                ['name' => 'Crear usuarios', 'description' => 'Puede crear nuevos usuarios'],
                ['name' => 'Editar usuarios', 'description' => 'Puede editar usuarios existentes'],
                ['name' => 'Eliminar usuarios', 'description' => 'Puede eliminar usuarios del sistema'],
                ['name' => 'Resetear contraseña de usuario', 'description' => 'Puede resetear contraseña de un usuario'],
            ],

            // --- Configuración (no CRUD) ---
            'Configuración' => [
                ['name' => 'Acceder configuración', 'description' => 'Puede acceder a la configuración global del sistema'],
                ['name' => 'Editar configuración', 'description' => 'Puede modificar parámetros avanzados del sistema'],
            ],

            // --- Reportes ---
            'Reportes' => [
                ['name' => 'Ver reportes', 'description' => 'Puede Ver reportes del sistema'],
                ['name' => 'Exportar reportes', 'description' => 'Puede exportar reportes en PDF o Excel'],
            ],
        ];

        // === Crear PERMISOS ===
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

        // === Crear ROLES ===
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
            'Ver productos', 'Crear productos', 'Editar productos', 'Eliminar productos',

            // Categorías
            'Ver categorías', 'Crear categorías', 'Editar categorías', 'Eliminar categorías',

            // Usuarios
            'Ver usuarios', 'Crear usuarios', 'Editar usuarios', 'Eliminar usuarios',
            'Resetear contraseña de usuario',

            // Reportes
            'Ver reportes', 'Exportar reportes',
        ]);

        // Vendedor → Acceso limitado
        $vendedor->syncPermissions([
            'Ver productos',
            'Crear productos',
            'Editar productos',

            'Ver categorías',

            'Ver usuarios',
        ]);

        // Supervisor → Solo lectura del sistema
        $supervisor->syncPermissions([
            'Ver productos',
            'Ver categorías',
            'Ver usuarios',
            'Ver reportes',
        ]);

        // Almacenero → Inventario + lectura básica
        $almacenero->syncPermissions([
            'Ver productos',
            'Editar productos',
            'Ajustar stock',
        ]);
    }
}
