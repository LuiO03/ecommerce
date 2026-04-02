<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Cover;
use App\Models\Family;
use App\Models\Order;
use App\Models\Option;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Últimos pedidos (solo si tiene permiso)
        $orders = $user->can('ordenes.index')
            ? Cache::remember('dashboard_latest_orders', 60, function () {
                return Order::with('user')
                    ->latest()
                    ->take(3)
                    ->get();
            })
            : collect();

        return view('admin.dashboard', [
            'user' => $user,
            'orders' => $orders,

            // Métricas
            'totalCategories' => $this->countIfCan($user, 'categorias.index', 'count_categories', fn() => Category::count()),
            'totalFamilies'   => $this->countIfCan($user, 'familias.index', 'count_families', fn() => Family::count()),
            'totalCovers'     => $this->countIfCan($user, 'portadas.index', 'count_covers', fn() => Cover::count()),
            'totalProducts'   => $this->countIfCan($user, 'productos.index', 'count_products', fn() => Product::count()),
            'totalUsers'      => $this->countIfCan($user, 'usuarios.index', 'count_users', fn() => User::count()),
            'totalClients'    => $this->countIfCan($user, 'clientes.index', 'count_clients', fn() => User::role('Cliente')->count()),
            'totalRoles'      => $this->countIfCan($user, 'roles.index', 'count_roles', fn() => Role::count()),
            'totalPosts'      => $this->countIfCan($user, 'posts.index', 'count_posts', fn() => Post::count()),
            'totalOptions'    => $this->countIfCan($user, 'opciones.index', 'count_options', fn() => Option::count()),
            'totalDrivers'    => $this->countIfCan($user, 'conductores.index', 'count_drivers', fn() => Driver::count()),

            // Configuración empresa
            'companyName' => Cache::remember('company_name', 3600, function () {
                return optional(CompanySetting::first())->name ?? 'Mi Empresa';
            }),
        ]);
    }

    /**
     * Retorna un conteo solo si el usuario tiene permiso.
     * Usa cache para optimizar rendimiento.
     */
    private function countIfCan($user, $permission, $cacheKey, $callback)
    {
        if (!$user->can($permission)) {
            return null;
        }

        return Cache::remember($cacheKey, 60, $callback);
    }
}
