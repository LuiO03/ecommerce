<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
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

        return view('site.carts.show', compact('cart'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cart $cart)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cart $cart)
    {
        //
    }

    /**
     * Vaciar completamente el carrito activo del usuario autenticado.
     */
    public function destroy()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $cart = Cart::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if ($cart) {
            // Eliminar todas las líneas del carrito
            $cart->items()->delete();

            // Resetear contadores
            $cart->items_count = 0;
            $cart->items_quantity = 0;
            $cart->save();
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Carrito limpiado',
            'message' => 'Todos los productos se han eliminado de tu carrito.',
        ]);

        return redirect()->route('carts.show');
    }

    /**
     * Actualizar la cantidad de un item del carrito.
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if (!$cartItem->cart || $cartItem->cart->user_id !== Auth::id()) {
            abort(403);
        }

        $oldQuantity = (int) $cartItem->quantity;
        $newQuantity = (int) $request->integer('quantity');

        // Limitar por stock de la variante si aplica
        $variant = $cartItem->variant;
        if ($variant) {
            $stock = (int) $variant->stock;
            if ($stock > 0 && $newQuantity > $stock) {
                $newQuantity = $stock;
            }
        }

        if ($newQuantity === $oldQuantity) {
            return redirect()->route('carts.show');
        }

        $diff = $newQuantity - $oldQuantity;

        $cartItem->update([
            'quantity' => $newQuantity,
        ]);

        $cart = $cartItem->cart;
        if ($cart) {
            $cart->items_quantity = max(0, (int) $cart->items_quantity + $diff);
            $cart->save();
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Cantidad actualizada',
            'message' => 'La cantidad del producto en tu carrito se ha actualizado.',
        ]);

        return redirect()->route('carts.show');
    }

    /**
     * Eliminar una línea del carrito.
     */
    public function destroyItem(CartItem $cartItem)
    {
        if (!$cartItem->cart || $cartItem->cart->user_id !== Auth::id()) {
            abort(403);
        }

        $cart = $cartItem->cart;
        $removedQuantity = (int) $cartItem->quantity;

        $cartItem->delete();

        if ($cart) {
            $cart->decrement('items_count');
            $cart->decrement('items_quantity', $removedQuantity);

            if ($cart->items_count < 0 || $cart->items_quantity < 0) {
                $cart->items_count = max(0, (int) $cart->items_count);
                $cart->items_quantity = max(0, (int) $cart->items_quantity);
            }

            $cart->save();
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto eliminado',
            'message' => 'El producto se ha eliminado de tu carrito.',
        ]);

        return redirect()->route('carts.show');
    }
}
