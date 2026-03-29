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
            $table->string('pdf_path')->nullable();

            $table->string('order_number')->unique(); // código tipo ORD-0001
            $table->decimal('total', 10, 2);
            $table->decimal('subtotal', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->default(0);

            $table->enum('status', [
                'pending',     // pendiente
                'paid',        // pagado
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
            $table->string('payment_status')->default('pending');

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
