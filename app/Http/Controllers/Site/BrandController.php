<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Brand;

class BrandController extends Controller
{
    public function show(Brand $brand)
    {
        $breadcrumbItems = [
            [
                'label' => 'Tienda',
                'url' => route('site.shop.index'),
                'icon' => 'ri-store-2-fill',
            ],
            [
                'label' => $brand->name,
                'icon' => 'ri-store-3-fill',
            ]
        ];

        return view('site.brands.show', compact('brand', 'breadcrumbItems'));
    }
}
