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
        Schema::create('claim_messages', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();

            $table->enum('claim_type', ['reclamo', 'queja']);
            $table->text('claim_detail');

            $table->uuid('idempotency_key')->nullable()->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('submitted_at')->nullable();

            $table->text('response')->nullable();

            $table->enum('status', ['new', 'read', 'replied'])->default('new');

            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_messages');
    }
};
