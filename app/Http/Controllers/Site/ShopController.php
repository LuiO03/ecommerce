<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;

class ShopController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', true)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
        return view('site.shop.index', compact('categories'));
    }
}
