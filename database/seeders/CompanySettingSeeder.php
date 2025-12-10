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
            'facebook_enabled' => true,
            'instagram_url' => 'https://instagram.com/geckomercce',
            'instagram_enabled' => true,
            'twitter_url' => 'https://twitter.com/geckomercce',
            'twitter_enabled' => true,
            'youtube_url' => 'https://www.youtube.com/@geckomercce',
            'youtube_enabled' => true,
            'tiktok_url' => 'https://www.tiktok.com/@geckomercce',
            'tiktok_enabled' => true,
            'linkedin_url' => 'https://www.linkedin.com/company/geckomercce',
            'linkedin_enabled' => true,
            'primary_color' => '#D11E4B',
            'secondary_color' => '#3D31B2',
            'logo_path' => 'geckomercce.png',
            'about' => 'Somos una tienda en línea enfocada en brindar la mejor experiencia de compra.',
            'terms_conditions' => '<h3>Términos y Condiciones</h3><p>Al utilizar nuestra plataforma, aceptas las políticas de uso y las condiciones comerciales descritas en este documento. Te recomendamos revisarlo periódicamente.</p>',
            'privacy_policy' => '<h3>Política de Privacidad</h3><p>Protegemos tu información personal cumpliendo con la normativa vigente. Consulta nuestra política para conocer cómo recopilamos, usamos y protegemos tus datos.</p>',
            'claims_book_information' => '<h3>Libro de Reclamaciones</h3><p>Si deseas registrar un reclamo o queja, puedes hacerlo a través de nuestro Libro de Reclamaciones virtual disponible en el sitio web.</p>',
        ]);
    }
}
