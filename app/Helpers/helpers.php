<?php

use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

if (!function_exists('fecha_hoy')) {
    function fecha_hoy()
    {
        Carbon::setLocale('es');
        return ucfirst(Carbon::now()->isoFormat('dddd, D [de] MMMM [de] YYYY'));
    }
}

if (!function_exists('company_setting')) {
    function company_setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = Cache::remember('company_settings', 60 * 30, function () {
            return CompanySetting::query()->first();
        });

        if (!$settings) {
            return $key ? $default : null;
        }

        if ($key === null) {
            return $settings;
        }

        return data_get($settings, $key, $default);
    }
}
