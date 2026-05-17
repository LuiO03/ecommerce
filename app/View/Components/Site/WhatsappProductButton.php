<?php

namespace App\View\Components\Site;

use App\Models\CompanySetting;
use App\Models\Product;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class WhatsappProductButton extends Component
{
    public Product $product;
    public string $whatsappUrl = '';
    public function __construct(Product $product)
    {
        $this->product = $product;
        $company = cache()->rememberForever('company_settings', function () {
            return CompanySetting::query()->first();
        });
        if (! $company?->whatsapp_number) {
            return;
        }
        // Limpiar número
        $phone = preg_replace('/\D+/', '', $company->whatsapp_number);
        // Código Perú
        if (strlen($phone) === 9 && str_starts_with($phone, '9')) {
            $phone = '51' . $phone;
        }

        if (empty($phone)) {
            return;
        }

        $companyName = $company->name ?? config('app.name');
        $productUrl = route('products.show', $product);
        $message = <<<TEXT
Hola buen día,

Estoy interesado en este producto:

- Producto: {$product->name}
- SKU: {$product->sku}

Aquí está el producto:
{$productUrl}

¿Podrían brindarme más información?
TEXT;

        $this->whatsappUrl =
            'https://api.whatsapp.com/send?phone=' .
            $phone .
            '&text=' .
            rawurlencode($message);
    }

    public function render(): View|Closure|string
    {
        return view('components.site.whatsapp-product-button');
    }
}
