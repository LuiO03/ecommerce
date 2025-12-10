<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanySettingRequest;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanySettingController extends Controller
{
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
            'setting' => $setting ?? new CompanySetting(),
            'hasLogo' => $hasLogo,
        ]);
    }

    /**
     * Update the company settings record.
     */
    public function update(UpdateCompanySettingRequest $request)
    {
        $setting = CompanySetting::query()->first();

        if (!$setting) {
            $setting = new CompanySetting();
        }

        $data = collect($request->validated());
        $removeLogo = $request->boolean('remove_logo');

        // Resolve existing logo path before any changes.
        $logoPath = $setting->logo_path;

        if ($removeLogo && $logoPath) {
            Storage::disk('public')->delete($logoPath);
            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($logoPath && Storage::disk('public')->exists($logoPath)) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $request->file('logo')->store('company', 'public');
        }

        $socialLinks = [
            'facebook' => $data->get('facebook_url'),
            'instagram' => $data->get('instagram_url'),
            'twitter' => $data->get('twitter_url'),
            'youtube' => $data->get('youtube_url'),
            'tiktok' => $data->get('tiktok_url'),
            'linkedin' => $data->get('linkedin_url'),
        ];

        $payload = $data
            ->except(['logo', 'remove_logo'])
            ->map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            })
            ->merge([
                'logo_path' => $logoPath,
                'social_links' => $socialLinks,
            ])
            ->toArray();

        $setting->fill($payload);
        $setting->save();

        Cache::forget('company_settings');

        Session::flash('toast', [
            'type' => 'success',
            'title' => 'Configuración actualizada',
            'message' => 'Los datos de la empresa se guardaron correctamente.',
        ]);

        return redirect()->route('admin.company-settings.edit');
    }
}
