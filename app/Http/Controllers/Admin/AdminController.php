<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Family;
use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalCategories' => Category::count(),
            'totalFamilies'   => Family::count(),
            'totalProducts'   => Product::count(),
            'totalUsers'      => User::count(),
            'totalPermissions' => Permission::count(),
            'totalRoles'      => \Spatie\Permission\Models\Role::count(),
        ]);
    }
    
}
