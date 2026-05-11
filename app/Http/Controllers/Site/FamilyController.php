<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\Category;

class FamilyController extends Controller
{

    public function show(Family $family)
    {
        // traer categoris de la familia
        $categories = Category::where('family_id', $family->id)
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return view('site.families.show', compact('family', 'categories'));
    }
}
