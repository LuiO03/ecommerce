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
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method');
            $table->string('purchase_number')->nullable();
            $table->string('request_hash', 64)->nullable();
            $table->enum('status', ['processing', 'approved', 'failed', 'conflict'])->default('processing');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_record_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->json('result_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payment_method', 'purchase_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
