<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cover;

class WellcomeController extends Controller
{
    public function index()
    {
        $covers = Cover::where('status', true)
            ->orderBy('position', 'asc')
            ->whereDate('start_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_at')
                ->orWhere('end_at', '>=', now());
            })
            ->get();
        return view('welcome', compact('covers'));
    }
}
