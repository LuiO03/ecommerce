<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Addresses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShippingController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $cart = Cart::with([
            'items.product.images',
            'items.product.category',
            'items.variant.features.option',
        ])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        $address = Addresses::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        return view('site.shipping.index', compact('cart', 'address'));
    }

    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $validated = $request->validate([
            'type' => 'required|in:home,office',
            'address_line' => 'required|string|min:5|max:255',
            'district' => 'required|string|max:120',
            'reference' => 'required|string|max:255',
            'receiver_name' => 'required|string|min:3|max:255',
            'receiver_last_name' => 'nullable|string|min:2|max:255',
            'receiver_phone' => 'required|string|min:6|max:20',
        ]);

        if (! $userId) {
            return redirect()->route('login');
        }

        $payload = [
            'type' => $validated['type'],
            'address_line' => ucfirst(mb_strtolower($validated['address_line'])),
            'district' => ucfirst(mb_strtolower($validated['district'])),
            'reference' => ucfirst(mb_strtolower($validated['reference'])),
            'receiver_type' => 1,
            'receiver_name' => ucwords(mb_strtolower($validated['receiver_name'])),
            'receiver_last_name' => ! empty($validated['receiver_last_name'])
                ? ucwords(mb_strtolower($validated['receiver_last_name']))
                : null,
            'receiver_phone' => $validated['receiver_phone'],
            'is_default' => true,
        ];

        $address = Addresses::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        if ($address) {
            $address->update($payload);
        } else {
            Addresses::create(
                array_merge($payload, [
                    'user_id' => $userId,
                ]),
            );
        }

        return redirect()
            ->route('checkout.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Dirección confirmada',
                'message' => 'Usaremos esta dirección para el envío de tu pedido.',
            ]);
    }

    public function edit(Addresses $address)
    {
        if (! Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $userId = Auth::id();

        $cart = Cart::with([
            'items.product.images',
            'items.product.category',
            'items.variant.features.option',
        ])
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        $addresses = Addresses::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        $editingAddress = $address;

        return view('site.shipping.index', compact('cart', 'addresses', 'editingAddress'));
    }

    public function update(Request $request, Addresses $address)
    {
        if (! Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $userId = Auth::id();

        $validated = $request->validate([
            'type' => 'required|in:home,office',
            'address_line' => 'required|string|min:5|max:255',
            'district' => 'required|string|max:120',
            'reference' => 'required|string|max:255',
            'receiver_name' => 'required|string|min:3|max:255',
            'receiver_last_name' => 'nullable|string|min:2|max:255',
            'receiver_phone' => 'required|string|min:6|max:20',
            'is_default' => 'sometimes|boolean',
        ]);

        $isDefault = $request->boolean('is_default');

        if ($isDefault) {
            Addresses::where('user_id', $userId)->update(['is_default' => false]);
        }

        $address->update([
            'type' => $validated['type'],
            'address_line' => ucfirst(mb_strtolower($validated['address_line'])),
            'district' => ucfirst(mb_strtolower($validated['district'])),
            'reference' => ucfirst(mb_strtolower($validated['reference'])),
            'receiver_name' => ucwords(mb_strtolower($validated['receiver_name'])),
            'receiver_last_name' => ! empty($validated['receiver_last_name'])
                ? ucwords(mb_strtolower($validated['receiver_last_name']))
                : null,
            'receiver_phone' => $validated['receiver_phone'],
            'is_default' => $isDefault,
        ]);

        return redirect()
            ->route('shipping.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Dirección actualizada',
                'message' => 'Tu dirección de envío se ha actualizado correctamente.',
            ]);
    }

    public function setDefault(Addresses $address)
    {
        if (! Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $userId = Auth::id();

        Addresses::where('user_id', $userId)->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return redirect()
            ->route('shipping.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Dirección predeterminada',
                'message' => 'Hemos actualizado tu dirección de envío predeterminada.',
            ]);
    }

    public function destroy(Addresses $address)
    {
        if (! Auth::check() || $address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return redirect()
            ->route('shipping.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Dirección eliminada',
                'message' => 'La dirección de envío se ha eliminado correctamente.',
            ]);
    }
}
