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
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->uuid('idempotency_key')->nullable()->unique()->after('message');
            $table->string('ip_address', 45)->nullable()->after('idempotency_key');
            $table->string('user_agent')->nullable()->after('ip_address');
            $table->timestamp('submitted_at')->nullable()->after('user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn([
                'idempotency_key',
                'ip_address',
                'user_agent',
                'submitted_at',
            ]);
        });
    }
};
