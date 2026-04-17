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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained('addresses')->nullOnDelete(); // Solo se llena si es delivery
            $table->string('pickup_store_code')->nullable();
            $table->string('pdf_path')->nullable();

            $table->string('order_number')->unique(); // código tipo ORD-0001
            $table->decimal('total', 10, 2);
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);

            // Método de entrega: delivery a domicilio o recojo en tienda
            $table->enum('delivery_type', [
                'delivery', // envío a domicilio
                'pickup',   // recojo en tienda
            ])->default('delivery');

            $table->enum('status', [
                'pending',     // pendiente
                'processing',  // en proceso
                'shipped',     // enviado
                'delivered',   // entregado
                'cancelled',    // cancelado
                'refunded'    // reembolsado
            ])->default('pending');

            $table->string('shipping_address');
            $table->string('shipping_city')->nullable();
            $table->string('shipping_phone')->nullable();

            $table->string('payment_method')->nullable()->default(1); // niubiz, yape, etc.
            // pending, authorized, captured, failed, refunded, etc.
            $table->string('payment_id')->nullable(); // id de transacción del gateway
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
