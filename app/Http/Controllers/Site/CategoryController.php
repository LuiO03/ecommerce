<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    public function show(Category $category)
    {
        $family = $category->family;
        $breadcrumbItems = [];

        if ($family) {
            $breadcrumbItems[] = [
                'label' => $family->name,
                'url' => route('families.show', $family),
            ];
        }

        $parents = [];
        $current = $category->parent;
        while ($current) {
            $parents[] = $current;
            $current = $current->parent;
        }

        foreach (array_reverse($parents) as $parent) {
            $breadcrumbItems[] = [
                'label' => $parent->name,
                'url' => route('categories.show', $parent),
            ];
        }

        $breadcrumbItems[] = [
            'label' => $category->name,
            'icon' => 'ri-price-tag-3-fill',
        ];

        return view('site.categories.show', compact('category', 'family', 'breadcrumbItems'));
    }
}
