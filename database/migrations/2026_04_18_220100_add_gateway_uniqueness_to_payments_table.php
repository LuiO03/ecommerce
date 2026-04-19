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
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['order_id', 'status'], 'payments_order_status_idx');
            $table->index(['provider', 'status'], 'payments_provider_status_idx');
            $table->unique(['provider', 'transaction_id'], 'payments_provider_transaction_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_provider_transaction_id_unique');
            $table->dropIndex('payments_order_status_idx');
            $table->dropIndex('payments_provider_status_idx');
        });
    }
};
