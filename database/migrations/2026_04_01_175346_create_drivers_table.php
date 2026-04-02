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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();

            // Un conductor está asociado a un único usuario
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');

            $table->enum('vehicle_type', ['motorcycle', 'car'])->default('motorcycle');
            $table->string('vehicle_plate')->nullable()->unique();

            $table->string('phone')->nullable();

            $table->enum('status', [
                'available', // disponible para asignar pedidos
                'busy',      // actualmente repartiendo
                'inactive',  // temporalmente inactivo
            ])->default('available');

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();  // obligatorio (Laravel los llena automáticamente)
            $table->softDeletes(); // opcional (para borrado lógico)

            // Índices útiles para filtros
            $table->index('status');

            // Relaciones opcionales
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
