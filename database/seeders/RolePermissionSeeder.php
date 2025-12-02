<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // === PERMISOS DEFINIDOS POR MÓDULO ===
        $permissions = [

            // --- Productos ---
            'productos' => [
                ['name' => 'productos.index', 'description' => 'Puede ver la lista de productos'],
                ['name' => 'productos.create', 'description' => 'Puede crear nuevos productos'],
                ['name' => 'productos.edit', 'description' => 'Puede editar productos existentes'],
                ['name' => 'productos.delete', 'description' => 'Puede eliminar productos del sistema'],
                ['name' => 'productos.adjust-stock', 'description' => 'Puede modificar existencias en inventario'],
            ],

            // --- Categorías ---
            'categorias' => [
                ['name' => 'categorias.index', 'description' => 'Puede ver la lista de categorías'],
                ['name' => 'categorias.create', 'description' => 'Puede crear nuevas categorías'],
                ['name' => 'categorias.edit', 'description' => 'Puede editar categorías existentes'],
                ['name' => 'categorias.delete', 'description' => 'Puede eliminar categorías del sistema'],
                ['name' => 'categorias.manage-tree', 'description' => 'Puede modificar la jerarquía de categorías'],
                ['name' => 'categorias.view-tree', 'description' => 'Puede ver la jerarquía visual de categorías'],
            ],

            // --- Roles ---
            'roles' => [
                ['name' => 'roles.index', 'description' => 'Puede ver la lista de roles'],
                ['name' => 'roles.create', 'description' => 'Puede crear nuevos roles'],
                ['name' => 'roles.edit', 'description' => 'Puede editar roles existentes'],
                ['name' => 'roles.delete', 'description' => 'Puede eliminar roles del sistema'],
                ['name' => 'roles.assign-permissions', 'description' => 'Puede asignar permisos a los roles'],
            ],

            // --- Permisos ---
            'permisos' => [
                ['name' => 'permisos.index', 'description' => 'Puede ver la lista de permisos'],
                ['name' => 'permisos.create', 'description' => 'Puede crear nuevos permisos'],
                ['name' => 'permisos.edit', 'description' => 'Puede editar permisos existentes'],
                ['name' => 'permisos.delete', 'description' => 'Puede eliminar permisos del sistema'],
            ],

            // --- Usuarios ---
            'usuarios' => [
                ['name' => 'usuarios.index', 'description' => 'Puede ver la lista de usuarios'],
                ['name' => 'usuarios.create', 'description' => 'Puede registrar nuevos usuarios'],
                ['name' => 'usuarios.edit', 'description' => 'Puede editar usuarios existentes'],
                ['name' => 'usuarios.delete', 'description' => 'Puede eliminar usuarios'],
                ['name' => 'usuarios.reset-password', 'description' => 'Puede resetear contraseñas de usuarios'],
                ['name' => 'usuarios.assign-roles', 'description' => 'Puede asignar roles a los usuarios'],
            ],

            // --- Configuración ---
            'configuracion' => [
                ['name' => 'configuracion.view', 'description' => 'Puede acceder a la configuración del sistema'],
                ['name' => 'configuracion.edit', 'description' => 'Puede modificar parámetros avanzados del sistema'],
            ],

            // --- Reportes ---
            'reportes' => [
                ['name' => 'reportes.index', 'description' => 'Puede ver reportes del sistema'],
                ['name' => 'reportes.export', 'description' => 'Puede exportar reportes (PDF, Excel)'],
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
                'description' => 'Control total del sistema.',
            ]
        );

        $admin = Role::firstOrCreate(
            ['name' => 'Administrador'],
            [
                'guard_name' => 'web',
                'description' => 'Gestión avanzada, excepto configuración crítica.',
            ]
        );

        $vendedor = Role::firstOrCreate(
            ['name' => 'Vendedor'],
            [
                'guard_name' => 'web',
                'description' => 'Gestión de ventas y productos.',
            ]
        );

        $supervisor = Role::firstOrCreate(
            ['name' => 'Supervisor'],
            [
                'guard_name' => 'web',
                'description' => 'Acceso principalmente de lectura.',
            ]
        );

        $almacenero = Role::firstOrCreate(
            ['name' => 'Almacenero'],
            [
                'guard_name' => 'web',
                'description' => 'Control de inventario y existencias.',
            ]
        );

        // === ASIGNACIÓN DE PERMISOS ===

        // Superadmin → TODO
        $superadmin->syncPermissions(Permission::all());

        // Administrador
        $admin->syncPermissions([
            // Productos
            'productos.index', 'productos.create', 'productos.edit', 'productos.delete',

            // Categorías
            'categorias.index', 'categorias.create', 'categorias.edit', 'categorias.delete',

            // Usuarios
            'usuarios.index', 'usuarios.create', 'usuarios.edit', 'usuarios.delete',
            'usuarios.reset-password',

            // Reportes
            'reportes.index', 'reportes.export',
        ]);

        // Vendedor
        $vendedor->syncPermissions([
            'productos.index',
            'productos.create',
            'productos.edit',
            'categorias.index',
            'usuarios.index',
        ]);

        // Supervisor (solo lectura)
        $supervisor->syncPermissions([
            'productos.index',
            'categorias.index',
            'usuarios.index',
            'reportes.index',
        ]);

        // Almacenero
        $almacenero->syncPermissions([
            'productos.index',
            'productos.edit',
            'productos.adjust-stock',
        ]);
    }
}
