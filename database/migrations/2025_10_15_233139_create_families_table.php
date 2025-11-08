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
        Schema::create('families', function (Blueprint $table) {
            $table->id();

            // Campos principales
            $table->string('name');             // obligatorio (NOT NULL)
            $table->string('slug')->unique()->index();   // obligatorio (NOT NULL y único)

            // Campos adicionales
            $table->text('description')->nullable(); // opcional
            $table->string('image')->nullable();    // opcional

            // Estado (activo/inactivo)
            $table->boolean('status')->default(false)->index();; // obligatorio, con valor por defecto

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();  // obligatorio (Laravel los llena automáticamente)
            $table->softDeletes(); // opcional (para borrado lógico)

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
        Schema::dropIfExists('families');
    }
};
