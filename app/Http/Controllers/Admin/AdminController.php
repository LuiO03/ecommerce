<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CompanySetting;
use App\Models\Cover;
use App\Models\Family;
use App\Models\Option;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return view('admin.dashboard', [
            'totalCategories' => $user->can('categorias.index') ? Category::count() : null,
            'totalFamilies' => $user->can('familias.index') ? Family::count() : null,
            'totalCovers' => $user->can('portadas.index') ? Cover::count() : null,
            'totalProducts' => $user->can('productos.index') ? Product::count() : null,
            'totalUsers' => $user->can('usuarios.index') ? User::count() : null,
            'totalClients' => $user->can('clientes.index')? User::role('Cliente')->count(): null,
            'totalRoles' => $user->can('roles.index') ? Role::count() : null,
            'totalPosts' => $user->can('posts.index') ? Post::count() : null,
            'totalOptions' => $user->can('opciones.index') ? Option::count() : null,
            // enviar el nombre de la empresa a la vista
            'companyName' => optional(CompanySetting::first())->name ?? 'Mi Empresa',
        ]);
    }
}
