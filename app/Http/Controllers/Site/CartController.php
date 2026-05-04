<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Cart\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

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
     *
     * Recupera el carrito activo del usuario autenticado con todas las relaciones necesarias.
     * Optimizado con eager loading para evitar N+1 queries.
     */
    public function show()
    {
        $userId = Auth::id();
        $cart = null;

        if ($userId) {
            $cart = Cart::with([
                'items.product.images',
                'items.product.brand',
                'items.product.category',
                'items.variant.images',
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
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $cart = Cart::where('user_id', Auth::id())
            ->where('is_active', true)
            ->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->update([
                'items_count' => 0,
                'items_quantity' => 0,
            ]);
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
     *
     * @param Request $request
     * @param CartItem $cartItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        // Validar propiedad del carrito
        $this->cartService->validateOwnership($cartItem->cart, Auth::id());

        $oldQuantity = (int) $cartItem->quantity;
        $newQuantity = min($request->integer('quantity'), $this->cartService->getItemMaxQuantity($cartItem));

        if ($newQuantity === $oldQuantity) {
            return redirect()->route('carts.show');
        }

        $diff = $newQuantity - $oldQuantity;

        $cartItem->update(['quantity' => $newQuantity]);

        $cart = $cartItem->cart;
        if ($cart) {
            $cart->update([
                'items_quantity' => max(0, (int) $cart->items_quantity + $diff),
            ]);
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
     *
     * @param CartItem $cartItem
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroyItem(CartItem $cartItem)
    {
        // Validar propiedad del carrito
        $this->cartService->validateOwnership($cartItem->cart, Auth::id());

        $cart = $cartItem->cart;
        $removedQuantity = (int) $cartItem->quantity;

        $cartItem->delete();

        if ($cart) {
            $cart->update([
                'items_count' => max(0, (int) $cart->items_count - 1),
                'items_quantity' => max(0, (int) $cart->items_quantity - $removedQuantity),
            ]);
        }

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Producto eliminado',
            'message' => 'El producto se ha eliminado de tu carrito.',
        ]);

        return redirect()->route('carts.show');
    }
}
