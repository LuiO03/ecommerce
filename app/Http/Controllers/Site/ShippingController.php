<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $cart = null;

        if ($userId) {
            $cart = Cart::with([
                'items.product.images',
                'items.product.category',
                'items.variant.features.option',
            ])
                ->where('user_id', $userId)
                ->where('is_active', true)
                ->first();
        }

        return view('site.shipping.index', compact('cart'));
    }
}
