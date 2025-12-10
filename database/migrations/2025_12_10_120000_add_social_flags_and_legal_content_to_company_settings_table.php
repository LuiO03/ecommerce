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
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->boolean('facebook_enabled')->default(true)->after('facebook_url');
            $table->boolean('instagram_enabled')->default(true)->after('instagram_url');
            $table->boolean('twitter_enabled')->default(true)->after('twitter_url');
            $table->boolean('youtube_enabled')->default(true)->after('youtube_url');
            $table->boolean('tiktok_enabled')->default(true)->after('tiktok_url');
            $table->boolean('linkedin_enabled')->default(true)->after('linkedin_url');

            $table->longText('terms_conditions')->nullable()->after('secondary_color');
            $table->longText('privacy_policy')->nullable()->after('terms_conditions');
            $table->longText('claims_book_information')->nullable()->after('privacy_policy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'facebook_enabled',
                'instagram_enabled',
                'twitter_enabled',
                'youtube_enabled',
                'tiktok_enabled',
                'linkedin_enabled',
                'terms_conditions',
                'privacy_policy',
                'claims_book_information',
            ]);
        });
    }
};
