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
        $setting = CompanySetting::first() ?? new CompanySetting();

        $data = collect($request->validated());
        $removeLogo = $request->boolean('remove_logo');

        // Resolve existing logo path before any changes.
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

            $fileName = $slug . '.png';

            $logoPath = $request->file('logo')->storeAs('company', $fileName, 'public');
        }

        $platforms = ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin'];

        $socialLinks = [];

        foreach ($platforms as $platform) {
            $socialLinks[$platform] = $data->get("{$platform}_url");
        }

        $payload = $data
            ->except(['logo', 'remove_logo'])
            ->map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            })
            ->merge([
                'logo_path' => $logoPath,
                'social_links' => $socialLinks,
                'facebook_enabled' => $request->boolean('facebook_enabled'),
                'instagram_enabled' => $request->boolean('instagram_enabled'),
                'twitter_enabled' => $request->boolean('twitter_enabled'),
                'youtube_enabled' => $request->boolean('youtube_enabled'),
                'tiktok_enabled' => $request->boolean('tiktok_enabled'),
                'linkedin_enabled' => $request->boolean('linkedin_enabled'),
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
