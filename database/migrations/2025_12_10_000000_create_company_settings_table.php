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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */
            $table->string('name');
            $table->boolean('show_company_name_in_ui')->default(true);

            $table->string('legal_name')->nullable();
            $table->string('ruc', 11)->nullable();
            $table->string('slogan')->nullable();

            $table->text('about')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Branding
            |--------------------------------------------------------------------------
            */
            $table->string('logo_path')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Contacto
            |--------------------------------------------------------------------------
            */
            $table->string('email')->nullable();
            $table->string('support_email')->nullable();

            $table->string('phone')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('whatsapp_number')->nullable();

            $table->string('address')->nullable();
            $table->string('website')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Redes sociales
            |--------------------------------------------------------------------------
            */

            $table->string('facebook_url')->nullable();
            $table->boolean('facebook_enabled')->default(true);

            $table->string('instagram_url')->nullable();
            $table->boolean('instagram_enabled')->default(true);

            $table->string('twitter_url')->nullable();
            $table->boolean('twitter_enabled')->default(true);

            $table->string('youtube_url')->nullable();
            $table->boolean('youtube_enabled')->default(true);

            $table->string('tiktok_url')->nullable();
            $table->boolean('tiktok_enabled')->default(true);

            $table->string('linkedin_url')->nullable();
            $table->boolean('linkedin_enabled')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Ubicación
            |--------------------------------------------------------------------------
            */
            $table->string('google_maps_url')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Configuración comercial
            |--------------------------------------------------------------------------
            */
            $table->decimal('shipping_cost_delivery', 10, 2)->default(5);

            /*
            |--------------------------------------------------------------------------
            | Documentos legales
            |--------------------------------------------------------------------------
            */
            $table->longText('terms_conditions')->nullable();
            $table->longText('privacy_policy')->nullable();
            $table->longText('claims_book_information')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
