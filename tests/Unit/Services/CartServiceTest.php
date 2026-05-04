<?php

namespace Tests\Unit\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use App\Services\Cart\CartService;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = app(CartService::class);
    }

    /**
     * Test: obtener precio descuentado sin descuento
     */
    public function test_get_item_discounted_price_without_discount()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'discount' => null,
        ]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $price = $this->cartService->getItemDiscountedPrice($item);

        $this->assertEquals(100.0, $price);
    }

    /**
     * Test: obtener precio descuentado con descuento
     */
    public function test_get_item_discounted_price_with_discount()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'discount' => 20,  // 20% de descuento
        ]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $price = $this->cartService->getItemDiscountedPrice($item);

        $this->assertEquals(80.0, $price);  // 100 - 20%
    }

    /**
     * Test: usa precio de variante en lugar de producto
     */
    public function test_get_item_discounted_price_uses_variant_price()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'discount' => 0,
        ]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'price' => 150,  // Precio diferente en variante
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $price = $this->cartService->getItemDiscountedPrice($item);

        $this->assertEquals(150.0, $price);  // Usa precio de variante
    }

    /**
     * Test: obtener total de línea (precio × cantidad)
     */
    public function test_get_item_line_total()
    {
        $product = Product::factory()->create([
            'price' => 100,
            'discount' => 10,
        ]);
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'price' => 100,
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $lineTotal = $this->cartService->getItemLineTotal($item);

        // 100 * (1 - 10/100) * 3 = 90 * 3 = 270
        $this->assertEquals(270.0, $lineTotal);
    }

    /**
     * Test: obtener porcentaje de descuento
     */
    public function test_get_product_discount_percent()
    {
        $product = Product::factory()->create(['discount' => 25]);

        $percent = $this->cartService->getProductDiscountPercent($product);

        $this->assertEquals(25.0, $percent);
    }

    /**
     * Test: detectar si producto tiene descuento
     */
    public function test_has_discount()
    {
        $productWithDiscount = Product::factory()->create(['discount' => 10]);
        $productNoDiscount = Product::factory()->create(['discount' => 0]);

        $this->assertTrue($this->cartService->hasDiscount($productWithDiscount));
        $this->assertFalse($this->cartService->hasDiscount($productNoDiscount));
    }

    /**
     * Test: obtener cantidad máxima basada en stock
     */
    public function test_get_item_max_quantity_respects_stock()
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create([
            'product_id' => $product->id,
            'stock' => 50,
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $maxQty = $this->cartService->getItemMaxQuantity($item);

        $this->assertEquals(50, $maxQty);
    }

    /**
     * Test: cantidad máxima sin variante es amplia
     */
    public function test_get_item_max_quantity_without_variant()
    {
        $product = Product::factory()->create();
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => null,
        ]);

        $maxQty = $this->cartService->getItemMaxQuantity($item);

        $this->assertEquals(99, $maxQty);
    }

    /**
     * Test: obtener subtotal del carrito
     */
    public function test_get_cart_subtotal()
    {
        $user = $this->createUser();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        // Item 1: 100 * 2 = 200
        $product1 = Product::factory()->create(['price' => 100, 'discount' => 0]);
        $variant1 = Variant::factory()->create(['product_id' => $product1->id, 'price' => 100]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'variant_id' => $variant1->id,
            'quantity' => 2,
        ]);

        // Item 2: 200 * 0.8 * 1 = 160
        $product2 = Product::factory()->create(['price' => 200, 'discount' => 20]);
        $variant2 = Variant::factory()->create(['product_id' => $product2->id, 'price' => 200]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'variant_id' => $variant2->id,
            'quantity' => 1,
        ]);

        $subtotal = $this->cartService->getCartSubtotal($cart);

        // 200 + 160 = 360
        $this->assertEquals(360.0, $subtotal);
    }

    /**
     * Test: obtener resumen del carrito
     */
    public function test_get_cart_summary()
    {
        $user = $this->createUser();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        $product = Product::factory()->create(['price' => 100, 'discount' => 0]);
        $variant = Variant::factory()->create(['product_id' => $product->id, 'price' => 100]);

        CartItem::factory()->count(2)->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $summary = $this->cartService->getCartSummary($cart);

        $this->assertEquals(2, $summary->itemsCount);      // 2 items
        $this->assertEquals(6, $summary->itemsQuantity);   // 3 + 3 unidades
        $this->assertEquals(600.0, $summary->subtotal);    // 100 * 3 * 2
    }

    /**
     * Test: validar propiedad del carrito - éxito
     */
    public function test_validate_ownership_success()
    {
        $user = $this->createUser();
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        // No debe lanzar excepción
        $this->assertTrue($this->cartService->validateOwnership($cart, $user->id));
    }

    /**
     * Test: validar propiedad del carrito - fallo
     */
    public function test_validate_ownership_fails()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $cart = Cart::factory()->create(['user_id' => $user1->id]);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->cartService->validateOwnership($cart, $user2->id);
    }

    /**
     * Test: obtener imagen de item
     */
    public function test_get_item_image_from_variant()
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->has('images')->create([
            'product_id' => $product->id,
        ]);
        $item = CartItem::factory()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
        ]);

        $image = $this->cartService->getItemImage($item);

        $this->assertNotNull($image);
        $this->assertTrue($variant->images->contains($image));
    }

    /**
     * Helper: crear usuario de prueba
     */
    private function createUser()
    {
        return \App\Models\User::factory()->create();
    }
}
