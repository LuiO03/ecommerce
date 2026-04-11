<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Índice único compuesto para tipo + número de documento
            // Esto evita que se repita el mismo número para el mismo tipo.
            $table->unique(['document_type', 'document_number'], 'users_document_type_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_document_type_number_unique');
        });
    }
};
