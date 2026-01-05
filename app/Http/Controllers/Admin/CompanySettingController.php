<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanySettingRequest;
use App\Models\Audit;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Admin\UpdateCompanyContactRequest;
use App\Http\Requests\Admin\UpdateCompanyIdentityRequest;
use App\Http\Requests\Admin\UpdateCompanylegalRequest;
use App\Http\Requests\Admin\UpdateCompanyGeneralRequest;
use App\Http\Requests\Admin\UpdateCompanySocialRequest;

class CompanySettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:configuracion.edit')->only(['updateGeneral', 'updateIdentity', 'updateContact', 'updateSocial', 'updateLegal']);
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
                Storage::disk('public')->delete($logoPath);
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
            $socialLinks[$platform] = $data->get("{$platform}_url");
        }
        $setting->social_links = $socialLinks;
        $setting->facebook_enabled = $request->boolean('facebook_enabled');
        $setting->instagram_enabled = $request->boolean('instagram_enabled');
        $setting->twitter_enabled = $request->boolean('twitter_enabled');
        $setting->youtube_enabled = $request->boolean('youtube_enabled');
        $setting->tiktok_enabled = $request->boolean('tiktok_enabled');
        $setting->linkedin_enabled = $request->boolean('linkedin_enabled');
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
