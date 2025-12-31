<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySocialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'facebook_enabled' => ['required', 'boolean'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'instagram_enabled' => ['required', 'boolean'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'twitter_enabled' => ['required', 'boolean'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'youtube_enabled' => ['required', 'boolean'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'tiktok_enabled' => ['required', 'boolean'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'linkedin_enabled' => ['required', 'boolean'],
        ];
    }
}
