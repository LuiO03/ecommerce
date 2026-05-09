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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('sku')->unique()->nullable();
            $table->string('name');
            $table->string('slug')->unique()->index(); // URL amigable
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('discount')->nullable();

            // Stock mínimo por producto (nullable, usa config si es null)
            $table->unsignedInteger('min_stock')->nullable()->comment('Stock mínimo para alerta, si es null usa config');

            // Estado (activo/inactivo)
            $table->boolean('status')->default(true);

            // Relación con categoría
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('restrict');

            // Relación con marca
            $table->foreignId('brand_id')
                ->nullable()
                ->constrained('brands')
                ->onDelete('restrict');

            // Producto destacado manualmente
            $table->boolean('featured')
                ->default(false)
                ->index();

            // Cantidad total vendida
            $table->unsignedInteger('sales_count')
                ->default(0)
                ->index();

            // Cantidad de vistas
            $table->unsignedInteger('views_count')
                ->default(0)
                ->index();

            // Promedio de calificaciones
            $table->decimal('rating_avg', 3, 2)
                ->default(0)
                ->index();

            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Relaciones con usuarios
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
        Schema::dropIfExists('products');
    }
};
