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
        Schema::create('covers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image_path');
            $table->string('title')->nullable();
            // Campos de overlay de texto
            $table->text('overlay_text')->nullable();
            $table->text('overlay_subtext')->nullable();
            $table->enum('text_position', [
                'top-left', 'top-center', 'top-right',
                'center-left', 'center-center', 'center-right',
                'bottom-left', 'bottom-center', 'bottom-right'
            ])->default('center-center');
            $table->string('text_color')->default('#FFFFFF');
            // Campos de botón CTA
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->enum('button_style', ['primary', 'secondary', 'outline', 'white'])->default('primary');
            // Fechas de vigencia
            $table->datetime('start_at')->nullable();
            $table->datetime('end_at')->nullable();
            $table->integer('position')->default(0)->index();
            // Estado (activo/inactivo)
            $table->boolean('status')->default(false)->index();
            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            // Relaciones opcionales
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('covers');
    }
};
