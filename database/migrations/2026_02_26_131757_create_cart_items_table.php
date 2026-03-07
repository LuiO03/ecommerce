<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')
                ->constrained('carts')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade')
                ->comment('Producto base añadido al carrito');

            // Variante concreta elegida; opcional si el producto no tiene variantes
            $table->foreignId('variant_id')
                ->nullable()
                ->constrained('variants')
                ->nullOnDelete()
                ->comment('Variante seleccionada (talla, color, etc.) cuando aplique');

            // Solo cantidad; los precios se calcularán al momento del pedido
            $table->unsignedInteger('quantity')->default(1)->comment('Cantidad de unidades añadidas al carrito');

            $table->text('notes')->nullable()->comment('Notas opcionales asociadas a esta línea del carrito');

            $table->timestamps();
            $table->softDeletes();

            // Unicidad por carrito + producto + variante (ignorando eliminados lógicos)
            $table->unique(['cart_id', 'product_id', 'variant_id', 'deleted_at'], 'cart_product_variant_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
