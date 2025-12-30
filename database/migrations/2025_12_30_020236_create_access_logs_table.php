<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
        {
            Schema::create('access_logs', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete()
            ->index();

        $table->string('email')->nullable();

        // Acción que intentó realizar
        $table->string('action', 20); // login, logout

        // Resultado de la acción
        $table->string('status', 20); // success, failed

        $table->ipAddress('ip_address')->nullable();
        $table->text('user_agent')->nullable();

        $table->timestamp('created_at')->useCurrent();

        $table->index(['action', 'status']);
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
    }
};
