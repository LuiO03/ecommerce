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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('provider'); // niubiz, pagoefectivo, yape
            $table->string('transaction_id')->nullable(); // id del gateway

            $table->decimal('amount', 10, 2); // monto pagado (bruto)
            $table->decimal('fee', 10, 2)->default(0); // comisión
            $table->decimal('net_amount', 10, 2)->nullable(); // monto neto

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded'
            ])->default('pending');

            $table->timestamp('paid_at')->nullable();

            $table->json('response')->nullable(); // respuesta completa de Niubiz

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
