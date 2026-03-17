<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;

class ShippingController extends Controller
{
    public function index()
    {
        return view('site.shipping.index');
    }
}
