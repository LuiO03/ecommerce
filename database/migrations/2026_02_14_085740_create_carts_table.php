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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();

            // Usuario dueño del carrito actual de la tienda
            $table->foreignId('user_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

            // Métricas simples del carrito (se pueden recalcular fácilmente)
            $table->unsignedInteger('items_count')->default(0)->comment('Cantidad de líneas de ítems (productos distintos)');
            $table->unsignedInteger('items_quantity')->default(0)->comment('Unidades totales sumando todas las líneas del carrito');

            // Estado básico del carrito; se diferencia de un pedido real
            $table->boolean('is_active')->default(true)->index()->comment('Indica si el carrito está activo o ya fue consumido/descartado');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
