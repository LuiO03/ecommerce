<?php

namespace App\View\Components\Site;

use Closure;
use Illuminate\View\Component;
use App\Models\CompanySetting;
use Illuminate\Contracts\View\View;

class WhatsappFloatButton extends Component
{
    public string $whatsappUrl = '';

    public function __construct(
        public ?string $message = null
    ) {
        $company = cache()->rememberForever('company_settings', function () {
            return CompanySetting::query()->first();
        });

        if (! $company?->whatsapp_number) {
            return;
        }

        // Limpiar número
        $phone = preg_replace('/\D+/', '', $company->whatsapp_number);

        // Agregar código Perú
        if (strlen($phone) === 9 && str_starts_with($phone, '9')) {
            $phone = '51' . $phone;
        }

        // Validación mínima
        if (empty($phone)) {
            return;
        }

        $message = $this->message
            ?: 'Hola, quisiera más información.';

        $this->whatsappUrl =
            'https://api.whatsapp.com/send?phone=' .
            $phone .
            '&text=' .
            rawurlencode($message);
    }

    public function render(): View|Closure|string
    {
        return view('components.site.whatsapp-float-button');
    }
}
