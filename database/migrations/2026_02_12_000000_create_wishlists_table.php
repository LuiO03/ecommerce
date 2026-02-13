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
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();

            // Relación principal
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Datos principales de la wishlist
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();

            // Configuración y estado
            $table->boolean('is_public')->default(false)->index();
            $table->string('share_token', 64)->nullable()->unique()->comment('Token público para compartir la lista');
            $table->unsignedInteger('items_count')->default(0)->comment('Contador denormalizado de ítems en la lista');
            $table->boolean('status')->default(true)->index();

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Relaciones de auditoría
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
        Schema::dropIfExists('wishlists');
    }
};
