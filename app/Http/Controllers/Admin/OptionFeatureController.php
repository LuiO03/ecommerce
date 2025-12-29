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
        // Usar el partial visual del pill para index
        $featureArr = $feature->toArray();
        $featureArr['delete_url'] = route('admin.options.features.destroy', [$option, $feature]);
        $html = view('admin.options.partials.feature-pill', [
            'feature' => $featureArr,
            'isColorOption' => $isColorOption
        ])->render();
        return response()->json(['html' => $html]);
    }
}
