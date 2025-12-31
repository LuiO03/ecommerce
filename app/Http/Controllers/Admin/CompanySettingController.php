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

class CompanySettingController extends Controller
{
    /**
     * Actualiza la información general de la empresa.
     */
    public function updateGeneral(UpdateCompanySettingRequest $request)
    {
        try {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $data = collect($request->validated());
            $setting->name = $data->get('name');
            $setting->legal_name = $data->get('legal_name');
            $setting->ruc = $data->get('ruc');
            $setting->slogan = $data->get('slogan');
            $setting->about = $data->get('about');
            $setting->save();
            Cache::forget('company_settings');
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Información general actualizada',
                'message' => 'La información general se guardó correctamente.',
            ]);
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionGeneral";
        } catch (ValidationException $e) {
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionGeneral"
                ->withErrors($e->validator, 'general');
        }
    }

    /**
     * Actualiza la identidad visual (colores y logotipo).
     */
    public function updateIdentity(UpdateCompanySettingRequest $request)
    {
        try {
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
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Identidad actualizada',
                'message' => 'La identidad visual se guardó correctamente.',
            ]);
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionIdentity";
        } catch (ValidationException $e) {
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionIdentity"
                ->withErrors($e->validator, 'identity');
        }
    }

    /**
     * Actualiza los datos de contacto.
     */
    public function updateContact(UpdateCompanySettingRequest $request)
    {
        try {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $data = collect($request->validated());
            $setting->contact_email = $data->get('contact_email');
            $setting->contact_phone = $data->get('contact_phone');
            $setting->contact_address = $data->get('contact_address');
            $setting->save();
            Cache::forget('company_settings');
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Contacto actualizado',
                'message' => 'Los datos de contacto se guardaron correctamente.',
            ]);
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionContact";
        } catch (ValidationException $e) {
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionContact"
                ->withErrors($e->validator, 'contact');
        }
    }

    /**
     * Actualiza los enlaces de redes sociales.
     */
    public function updateSocial(UpdateCompanySettingRequest $request)
    {
        try {
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
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Redes sociales actualizadas',
                'message' => 'Los enlaces de redes sociales se guardaron correctamente.',
            ]);
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionSocial";
        } catch (ValidationException $e) {
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionSocial"
                ->withErrors($e->validator, 'social');
        }
    }

    /**
     * Actualiza los datos fiscales.
     */
    public function updateFiscal(UpdateCompanySettingRequest $request)
    {
        try {
            $setting = CompanySetting::first() ?? new CompanySetting;
            $data = collect($request->validated());
            $setting->fiscal_name = $data->get('fiscal_name');
            $setting->fiscal_rfc = $data->get('fiscal_rfc');
            $setting->fiscal_address = $data->get('fiscal_address');
            $setting->fiscal_regimen = $data->get('fiscal_regimen');
            $setting->save();
            Cache::forget('company_settings');
            Session::flash('toast', [
                'type' => 'success',
                'title' => 'Datos fiscales actualizados',
                'message' => 'Los datos fiscales se guardaron correctamente.',
            ]);
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionFiscal";
        } catch (ValidationException $e) {
            return redirect()->route('admin.company-settings.edit')."#companySettingsSectionFiscal"
                ->withErrors($e->validator, 'fiscal');
        }
    }

    /**
     * Show the company settings form.
     */
    public function edit()
    {
        $setting = CompanySetting::query()->first();

        $hasLogo = false;

        if ($setting && $setting->logo_path) {
            $hasLogo = Str::startsWith($setting->logo_path, ['http://', 'https://'])
                || Storage::disk('public')->exists($setting->logo_path);
        }

        return view('admin.company-settings.edit', [
            'setting' => $setting ?? new CompanySetting,
            'hasLogo' => $hasLogo,
        ]);
    }
}
