<?php

namespace App\Http\Controllers\Admin;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\UpdateCompanyContactRequest;
use App\Http\Requests\Admin\UpdateCompanyIdentityRequest;
use App\Http\Requests\Admin\UpdateCompanyLegalRequest;
use App\Http\Requests\Admin\UpdateCompanyGeneralRequest;
use App\Http\Requests\Admin\UpdateCompanySocialRequest;
use App\Http\Requests\Admin\UpdateCompanyShippingRequest;
use App\Http\Requests\Admin\UpdateCompanyMainRequest;

class CompanySettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:configuracion.edit')->only(['updateGeneral', 'updateIdentity', 'updateContact', 'updateShipping', 'updateSocial', 'updateLegal']);
        $this->middleware('can:configuracion.index')->only(['index']);
    }

    protected function recordCompanyAudit($request, CompanySetting $setting, string $event, ?array $oldValues, ?array $newValues): void
    {
        if ($oldValues !== null && $newValues !== null && $oldValues == $newValues) {
            return;
        }

        try {
            Audit::create([
                'user_id'        => Auth::id(),
                'event'          => $event,
                'auditable_type' => CompanySetting::class,
                'auditable_id'   => $setting->id,
                'old_values'     => $oldValues,
                'new_values'     => $newValues,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

        /**
     * Actualiza datos generales, contacto e identidad visual en un solo request.
     */
    public function updateMain(UpdateCompanyMainRequest $request)
    {
        $setting = CompanySetting::first() ?? new CompanySetting;
        $original = $setting->exists ? $setting->only([
            'name', 'legal_name', 'ruc', 'slogan', 'about',
            'email', 'support_email', 'phone', 'support_phone', 'address', 'website',
            'google_maps_url',
            'logo_path',
        ]) : null;

        $data = collect($request->validated());

        // General
        $setting->name = $data->get('name');
        $setting->legal_name = $data->get('legal_name');
        $setting->ruc = $data->get('ruc');
        $setting->slogan = $data->get('slogan');
        $setting->about = $data->get('about');

        // Contacto
        $setting->email = $data->get('email');
        $setting->support_email = $data->get('support_email');
        $setting->phone = $data->get('phone');
        $setting->support_phone = $data->get('support_phone');
        $setting->address = $data->get('address');
        $setting->website = $data->get('website');
        $setting->google_maps_url = $data->get('google_maps_url');

        // Identidad visual
        $removeLogo = $request->boolean('remove_logo');
        $logoPath = $setting->logo_path;
        if ($removeLogo && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }
        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            $companyName = $data->get('name') ?? $setting->name ?? 'company-logo';
            $slug = Str::slug($companyName, '-');
            if ($slug === '') {
                $slug = 'company-logo';
            }
            $fileName = $slug.'.png';
            $logoPath = $request->file('logo')->storeAs('company', $fileName, 'public');
        }
        $setting->logo_path = $logoPath;


        $setting->save();
        Cache::forget('company_settings');

        $this->recordCompanyAudit($request, $setting, 'company_main_updated', $original, [
            'name' => $setting->name,
            'legal_name' => $setting->legal_name,
            'ruc' => $setting->ruc,
            'slogan' => $setting->slogan,
            'about' => $setting->about,
            'email' => $setting->email,
            'support_email' => $setting->support_email,
            'phone' => $setting->phone,
            'support_phone' => $setting->support_phone,
            'address' => $setting->address,
            'website' => $setting->website,
            'google_maps_url' => $setting->google_maps_url,
            'logo_path' => $setting->logo_path,
        ]);

        return redirect()
            ->route('admin.company-settings.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Datos del negocio actualizados',
                'message' => 'La información general, contacto e identidad visual se guardaron correctamente.',
            ]);
    }
    /**
     * Actualiza la información general de la empresa.
     */
    public function updateGeneral(UpdateCompanyGeneralRequest $request)
    {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $original = $setting->exists ? $setting->only(['name', 'legal_name', 'ruc', 'slogan', 'about']) : null;

            $data = collect($request->validated());
            $setting->name = $data->get('name');
            $setting->legal_name = $data->get('legal_name');
            $setting->ruc = $data->get('ruc');
            $setting->slogan = $data->get('slogan');
            $setting->about = $data->get('about');
            $setting->save();
            Cache::forget('company_settings');

            $this->recordCompanyAudit($request, $setting, 'company_general_updated', $original, [
                'name'       => $setting->name,
                'legal_name' => $setting->legal_name,
                'ruc'        => $setting->ruc,
                'slogan'     => $setting->slogan,
                'about'      => $setting->about,
            ]);

            return redirect()
                ->route('admin.company-settings.index')
                ->with('toast', [
                    'type' => 'success',
                    'title' => 'Información general actualizada',
                    'message' => 'Los datos generales de la empresa se guardaron correctamente.',
                ]);

    }

        /**
     * Muestra una vista previa PDF de la boleta de venta con datos de ejemplo.
     */
    public function invoicePreview()
    {
        // Datos de ejemplo para la boleta
        $companyInfo = CompanySetting::first();
        $order = (object) [
            'order_number' => 'BOL-000001',
            'created_at' => now(),
            'subtotal' => 100.00,
            'shipping_cost' => 5.00,
            'total' => 105.00,
            'payment_method' => 'NIUBIZ',
            'payment_status' => 'Pagado',
            'delivery_type' => 'delivery',
            'shipping_address' => $companyInfo->address ?? 'Av. Ejemplo 123',
            'pickup_store_code' => null,
            'user' => (object) [
                'name' => 'Cliente Demo',
                'last_name' => 'Prueba',
                'email' => 'cliente@demo.com',
                'document_type' => 'DNI',
                'document_number' => '12345678',
            ],
            'items' => [
                (object) [
                    'product' => (object) ['name' => 'Producto de ejemplo'],
                    'variant' => null,
                    'unit_price' => 50.00,
                    'quantity' => 2,
                    'line_total' => 100.00,
                ],
            ],
        ];

        $pdf = Pdf::loadView('admin.export.order-invoice', [
            'order' => $order,
            'companyInfo' => $companyInfo,
        ])->setPaper('a4');

        return $pdf->stream('boleta-ejemplo.pdf');
    }

    /**
     * Actualiza la identidad visual (colores y logotipo).
     */
    public function updateIdentity(UpdateCompanyIdentityRequest $request)
    {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $original = $setting->exists ? $setting->only(['primary_color', 'secondary_color', 'logo_path']) : null;

            $data = collect($request->validated());
            $removeLogo = $request->boolean('remove_logo');
            $logoPath = $setting->logo_path;
            if ($removeLogo && $logoPath) {
                Storage::disk('public')->delete($logoPath);
                $logoPath = null;
            }
            if ($request->hasFile('logo')) {
                if ($logoPath) {
                    Storage::disk('public')->delete($logoPath);
                }

                $companyName = $data->get('name') ?? $setting->name ?? 'company-logo';
                $slug = Str::slug($companyName, '-');
                if ($slug === '') {
                    $slug = 'company-logo';
                }
                $fileName = $slug.'.png';
                $logoPath = $request->file('logo')->storeAs('company', $fileName, 'public');
            }
            $setting->primary_color = $data->get('primary_color');
            $setting->secondary_color = $data->get('secondary_color');
            $setting->logo_path = $logoPath;
            $setting->save();
            Cache::forget('company_settings');

            $this->recordCompanyAudit($request, $setting, 'company_identity_updated', $original, [
                'primary_color'   => $setting->primary_color,
                'secondary_color' => $setting->secondary_color,
                'logo_path'       => $setting->logo_path,
            ]);

            return redirect()
                ->route('admin.company-settings.index')
                ->with('toast', [
                    'type' => 'success',
                    'title' => 'Identidad visual actualizada',
                    'message' => 'Los datos de identidad visual se guardaron correctamente.',
                ]);
    }

    /**
     * Actualiza los datos de contacto.
     */
    public function updateContact(UpdateCompanyContactRequest $request)
    {
        $setting = CompanySetting::first() ?? new CompanySetting;

        $original = $setting->exists ? [
            'email'         => $setting->email,
            'support_email' => $setting->support_email,
            'phone'         => $setting->phone,
            'support_phone' => $setting->support_phone,
            'address'       => $setting->address,
            'website'       => $setting->website,
        ] : null;

        $data = $request->validated();

        $setting->email = $data['email'] ?? null;
        $setting->support_email  = $data['support_email'] ?? null;
        $setting->phone = $data['phone'] ?? null;
        $setting->support_phone = $data['support_phone'] ?? null;
        $setting->address = $data['address'] ?? null;
        $setting->website = $data['website'] ?? null;

        $setting->save();

        Cache::forget('company_settings');

        $this->recordCompanyAudit($request, $setting, 'company_contact_updated', $original, [
            'email'         => $setting->email,
            'support_email' => $setting->support_email,
            'phone'         => $setting->phone,
            'support_phone' => $setting->support_phone,
            'address'       => $setting->address,
            'website'       => $setting->website,
        ]);

        return redirect()
            ->route('admin.company-settings.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Contacto actualizado',
                'message' => 'Los datos de contacto se guardaron correctamente.',
            ]);
    }

    /**
     * Actualiza los costos de envío por tipo de entrega.
     */
    public function updateShipping(UpdateCompanyShippingRequest $request)
    {
        $setting = CompanySetting::first() ?? new CompanySetting;

        $original = $setting->exists ? [
            'shipping_cost_delivery' => $setting->shipping_cost_delivery,
        ] : null;

        $data = $request->validated();

        $setting->shipping_cost_delivery = (float) ($data['shipping_cost_delivery'] ?? config('products.shipping_cost_delivery', 5));
        $setting->save();

        Cache::forget('company_settings');

        $this->recordCompanyAudit($request, $setting, 'company_shipping_updated', $original, [
            'shipping_cost_delivery' => $setting->shipping_cost_delivery,
        ]);

        return redirect()
            ->route('admin.company-settings.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Costos de envío actualizados',
                'message' => 'La configuración de envío se guardó correctamente.',
            ]);
    }

    /**
     * Actualiza los enlaces de redes sociales.
     */
    public function updateSocial(UpdateCompanySocialRequest $request)
    {
        $setting = CompanySetting::first() ?? new CompanySetting;
        $original = $setting->exists ? [
            'social_links'      => $setting->social_links,
            'facebook_enabled'  => $setting->facebook_enabled,
            'instagram_enabled' => $setting->instagram_enabled,
            'twitter_enabled'   => $setting->twitter_enabled,
            'youtube_enabled'   => $setting->youtube_enabled,
            'tiktok_enabled'    => $setting->tiktok_enabled,
            'linkedin_enabled'  => $setting->linkedin_enabled,
        ] : null;
        $data = collect($request->validated());
        $platforms = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin'];
        $socialLinks = [];
        foreach ($platforms as $platform) {
            $url = $data->get("{$platform}_url");
            $socialLinks[$platform] = $url;
            // Guardar el campo *_url en el modelo
            $setting->setAttribute("{$platform}_url", $url);
            // Si la URL está vacía, forzar el enabled a false
            if (empty($url)) {
                $setting->setAttribute("{$platform}_enabled", false);
            } else {
                $setting->setAttribute("{$platform}_enabled", $request->boolean("{$platform}_enabled"));
            }
        }
        $setting->social_links = $socialLinks;
        $setting->save();
        Cache::forget('company_settings');

        $this->recordCompanyAudit($request, $setting, 'company_social_updated', $original, [
            'social_links'      => $setting->social_links,
            'facebook_enabled'  => $setting->facebook_enabled,
            'instagram_enabled' => $setting->instagram_enabled,
            'twitter_enabled'   => $setting->twitter_enabled,
            'youtube_enabled'   => $setting->youtube_enabled,
            'tiktok_enabled'    => $setting->tiktok_enabled,
            'linkedin_enabled'  => $setting->linkedin_enabled,
        ]);

        return redirect()->route('admin.company-settings.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Redes sociales actualizadas',
                'message' => 'Los enlaces de redes sociales se guardaron correctamente.',
            ]);
    }

    /**
     * Actualiza los datos legales.
     */
    public function updateLegal(UpdateCompanyLegalRequest $request)
    {
        $setting = CompanySetting::first() ?? new CompanySetting;
        $original = $setting->exists ? [
            'terms_conditions'       => $setting->terms_conditions,
            'privacy_policy'         => $setting->privacy_policy,
            'claims_book_information'=> $setting->claims_book_information,
        ] : null;
        $data = $request->validated();
        $setting->terms_conditions = $data['terms_conditions'] ?? null;
        $setting->privacy_policy = $data['privacy_policy'] ?? null;
        $setting->claims_book_information = $data['claims_book_information'] ?? null;
        $setting->save();
        Cache::forget('company_settings');

        $this->recordCompanyAudit($request, $setting, 'company_legal_updated', $original, [
            'terms_conditions'        => $setting->terms_conditions,
            'privacy_policy'          => $setting->privacy_policy,
            'claims_book_information' => $setting->claims_book_information,
        ]);
        return redirect()->route('admin.company-settings.index')
            ->with('toast', [
                'type' => 'success',
                'title' => 'Aspectos legales actualizados',
                'message' => 'Los documentos legales se guardaron correctamente.',
            ]);
    }

    /**
     * Show the company settings form.
     */
    public function index()
    {
        $setting = CompanySetting::query()->first();

        $hasLogo = false;

        if ($setting && $setting->logo_path) {
            $hasLogo = Str::startsWith($setting->logo_path, ['http://', 'https://'])
                || Storage::disk('public')->exists($setting->logo_path);
        }

        return view('admin.company-settings.index', [
            'setting' => $setting ?? new CompanySetting,
            'hasLogo' => $hasLogo,
        ]);
    }
}
