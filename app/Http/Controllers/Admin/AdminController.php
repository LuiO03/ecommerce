<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Cover;
use App\Models\Driver;
use App\Models\Family;
use App\Models\Option;
use App\Models\Order;
use App\Models\Post;
use App\Models\Product;
use App\Models\Variant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | Últimos pedidos
        |--------------------------------------------------------------------------
        */
        $orders = $user->can('ordenes.index')
            ? Cache::remember('dashboard_latest_orders', 60, function () {
                return Order::with('user')
                    ->latest()
                    ->take(5)
                    ->get();
            })
            : collect();

        /*
        |--------------------------------------------------------------------------
        | Métricas rápidas
        |--------------------------------------------------------------------------
        */
        $dashboardStats = [
            'totalSales' => $this->cacheRemember(
                'dashboard_total_sales',
                fn () => Order::whereHas('payments', function ($query) {
                    $query->where('status', 'paid');
                })->sum('total')
            ),

            'totalOrdersToday' => $this->cacheRemember(
                'dashboard_orders_today',
                fn () => Order::whereDate('created_at', now())->count()
            ),

            'totalPendingOrders' => $this->cacheRemember(
                'dashboard_pending_orders',
                fn () => Order::where('status', 'pending')->count()
            ),

            'totalLowStockProducts' => $this->cacheRemember(
                'dashboard_low_stock_products',
                fn() => Variant::where('stock', '<=', 5)
                    ->where('status', true)
                    ->count()
            ),

            'newClientsThisMonth' => $this->cacheRemember(
                'dashboard_new_clients_month',
                fn () => User::role('Cliente')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Conteos principales
        |--------------------------------------------------------------------------
        */
        $metrics = [
            'totalCategories' => $this->countIfCan(
                $user,
                'categorias.index',
                'count_categories',
                fn () => Category::count()
            ),

            'totalFamilies' => $this->countIfCan(
                $user,
                'familias.index',
                'count_families',
                fn () => Family::count()
            ),

            'totalCovers' => $this->countIfCan(
                $user,
                'portadas.index',
                'count_covers',
                fn () => Cover::count()
            ),

            'totalProducts' => $this->countIfCan(
                $user,
                'productos.index',
                'count_products',
                fn () => Product::count()
            ),

            'totalUsers' => $this->countIfCan(
                $user,
                'usuarios.index',
                'count_users',
                fn () => User::count()
            ),

            'totalClients' => $this->countIfCan(
                $user,
                'clientes.index',
                'count_clients',
                fn () => User::role('Cliente')->count()
            ),

            'totalRoles' => $this->countIfCan(
                $user,
                'roles.index',
                'count_roles',
                fn () => Role::count()
            ),

            'totalPosts' => $this->countIfCan(
                $user,
                'posts.index',
                'count_posts',
                fn () => Post::count()
            ),

            'totalOptions' => $this->countIfCan(
                $user,
                'opciones.index',
                'count_options',
                fn () => Option::count()
            ),

            'totalDrivers' => $this->countIfCan(
                $user,
                'conductores.index',
                'count_drivers',
                fn () => Driver::count()
            ),

            'totalBrands' => $this->countIfCan(
                $user,
                'marcas.index',
                'count_brands',
                fn () => Brand::count()
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Empresa
        |--------------------------------------------------------------------------
        */
        $company = Cache::remember('dashboard_company_info', 3600, function () {
            return CompanySetting::first();
        });

        return view('admin.dashboard', array_merge(
            [
                'user' => $user,
                'orders' => $orders,
                'company' => $company,
            ],
            $dashboardStats,
            $metrics
        ));
    }

    /**
     * Retorna conteo solo si el usuario tiene permiso.
     */
    private function countIfCan($user, string $permission, string $cacheKey, callable $callback)
    {
        if (! $user->can($permission)) {
            return null;
        }

        return Cache::remember($cacheKey, 60, $callback);
    }

    /**
     * Helper simple para cachear datos.
     */
    private function cacheRemember(string $key, callable $callback, int $seconds = 60)
    {
        return Cache::remember($key, $seconds, $callback);
    }
}
