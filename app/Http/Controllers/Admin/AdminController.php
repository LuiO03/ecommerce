<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Family;
use App\Models\Product;
use App\Models\User;
use App\Models\Post;
use Spatie\Permission\Models\Role;
use App\Models\CompanySetting;
use Spatie\Permission\Models\Permission;
use App\Models\Option;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalCategories' => Category::count(),
            'totalFamilies'   => Family::count(),
            'totalProducts'   => Product::count(),
            'totalUsers'      => User::count(),
            'totalRoles'      => Role::count(),
            'totalPosts'      => Post::count(),
            'totalOptions'    => Option::count(),
            // enviar el nombre de la empresa a la vista
            'companyName' => optional(CompanySetting::first())->name ?? 'Mi Empresa',
        ]);
    }

}
