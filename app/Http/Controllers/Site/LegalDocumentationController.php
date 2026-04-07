<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;

class LegalDocumentationController extends Controller
{
    protected function companySettings(): ?CompanySetting
    {
        if (function_exists('company_setting')) {
            /** @var \App\Models\CompanySetting|null $settings */
            $settings = company_setting();
            if ($settings instanceof CompanySetting) {
                return $settings;
            }
        }

        return CompanySetting::first();
    }

    public function terms()
    {
        $settings = $this->companySettings();

        abort_unless($settings, 404);

        return view('site.legal.terms', [
            'companySettings' => $settings,
            'content' => $settings->terms_conditions,
        ]);
    }

    public function privacy()
    {
        $settings = $this->companySettings();

        abort_unless($settings, 404);

        return view('site.legal.privacy', [
            'companySettings' => $settings,
            'content' => $settings->privacy_policy,
        ]);
    }

    public function claims()
    {
        $settings = $this->companySettings();

        abort_unless($settings, 404);

        return view('site.legal.claims', [
            'companySettings' => $settings,
            'content' => $settings->claims_book_information,
        ]);
    }
}
