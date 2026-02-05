<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use App\Models\Option;
use App\Models\Category;
use App\Models\Product;
use App\Models\Feature;
use App\Models\Variant;

class FamilyController extends Controller
{

    public function show(Family $family)
    {
        /*$options = Option::whereHas('products.category', function ($query) use ($family){
            $query->where('family_id', $family->id);
        })->with([
            'features' => function ($query) use ($family){
                $query->whereHas('variants.product.category', function ($query) use ($family){
                    $query->where('family_id', $family->id);
                });
            }
        ])
        ->get();
        // Lógica para mostrar la familia según el parámetro recibido
        return $options;*/
        return view('site.families.show', compact('family'));
    }
}
