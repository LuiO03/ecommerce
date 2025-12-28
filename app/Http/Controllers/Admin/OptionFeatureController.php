<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Feature;
use Illuminate\Http\Request;

class OptionFeatureController extends Controller
{
    public function renderItem(Option $option, Feature $feature)
    {
        $isColorOption = $option->isColor();
        $index = 0; // El index no es relevante para el render inline, pero se puede ajustar si es necesario
        $html = view('admin.options.partials.feature-item', compact('feature', 'index', 'isColorOption'))->render();
        return response()->json(['html' => $html]);
    }
}
