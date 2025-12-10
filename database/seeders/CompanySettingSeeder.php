<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CompanySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (CompanySetting::count() > 0) {
            return;
        }

        Cache::forget('company_settings');

        CompanySetting::create([
            'name' => 'GeckoMercce',
            'legal_name' => 'GeckoMercce S.A.C.',
            'ruc' => '12345678901',
            'slogan' => 'Tu ecommerce inteligente',
            'email' => 'contacto@geckomercce.com',
            'support_email' => 'soporte@geckomercce.com',
            'phone' => '+51 999 888 777',
            'support_phone' => '+51 977 888 111',
            'address' => 'Av. Principal 123, Lima, Perú',
            'website' => 'https://www.geckomercce.com',
            'social_links' => [
                'facebook' => 'https://facebook.com/geckomercce',
                'instagram' => 'https://instagram.com/geckomercce',
                'twitter' => 'https://twitter.com/geckomercce',
                'youtube' => 'https://www.youtube.com/@geckomercce',
                'tiktok' => 'https://www.tiktok.com/@geckomercce',
                'linkedin' => 'https://www.linkedin.com/company/geckomercce',
            ],
            'facebook_url' => 'https://facebook.com/geckomercce',
            'instagram_url' => 'https://instagram.com/geckomercce',
            'twitter_url' => 'https://twitter.com/geckomercce',
            'youtube_url' => 'https://www.youtube.com/@geckomercce',
            'tiktok_url' => 'https://www.tiktok.com/@geckomercce',
            'linkedin_url' => 'https://www.linkedin.com/company/geckomercce',
            'primary_color' => '#10B981',
            'secondary_color' => '#0EA5E9',
            'about' => 'Somos una tienda en línea enfocada en brindar la mejor experiencia de compra.',
        ]);
    }
}
