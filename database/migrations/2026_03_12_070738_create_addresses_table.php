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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['home', 'office']);
            $table->string('address_line');
            $table->string('district');
            $table->string('reference');
            $table->integer('receiver_type')->default(1)->comment('1: Yo, 2: Otra Persona');
            $table->string('receiver_name');
            $table->string('receiver_last_name')->nullable();
            $table->string('receiver_phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
