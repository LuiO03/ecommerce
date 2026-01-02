<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanySettingRequest;
use App\Models\CompanySetting;
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
    /**
     * Actualiza la información general de la empresa.
     */
    public function updateGeneral(UpdateCompanyGeneralRequest $request)
    {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $data = collect($request->validated());
            $setting->name = $data->get('name');
            $setting->legal_name = $data->get('legal_name');
            $setting->ruc = $data->get('ruc');
            $setting->slogan = $data->get('slogan');
            $setting->about = $data->get('about');
            $setting->save();
            Cache::forget('company_settings');

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

        $data = $request->validated();

        $setting->email = $data['email'] ?? null;
        $setting->support_email  = $data['support_email'] ?? null;
        $setting->phone = $data['phone'] ?? null;
        $setting->support_phone = $data['support_phone'] ?? null;
        $setting->address = $data['address'] ?? null;
        $setting->website = $data['website'] ?? null;

        $setting->save();

        Cache::forget('company_settings');

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
        $data = $request->validated();
        $setting->terms_conditions = $data['terms_conditions'] ?? null;
        $setting->privacy_policy = $data['privacy_policy'] ?? null;
        $setting->claims_book_information = $data['claims_book_information'] ?? null;
        $setting->save();
        Cache::forget('company_settings');
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
