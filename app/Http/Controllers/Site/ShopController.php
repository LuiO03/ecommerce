<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class ShopController extends Controller
{
    public function index()
    {
        return view('site.shop.index');
    }
}
