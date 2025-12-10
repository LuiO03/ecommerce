<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanySettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:1500'],
            'email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'support_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
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
            'primary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'secondary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'logo' => ['nullable', 'image', 'mimes:png', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'terms_conditions' => ['nullable', 'string'],
            'privacy_policy' => ['nullable', 'string'],
            'claims_book_information' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom attribute names for validation errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre comercial',
            'legal_name' => 'razón social',
            'ruc' => 'RUC',
            'slogan' => 'eslogan',
            'about' => 'descripción',
            'email' => 'correo principal',
            'support_email' => 'correo de soporte',
            'phone' => 'teléfono principal',
            'support_phone' => 'teléfono de soporte',
            'address' => 'dirección',
            'website' => 'sitio web',
            'facebook_url' => 'Facebook',
            'instagram_url' => 'Instagram',
            'twitter_url' => 'Twitter',
            'youtube_url' => 'YouTube',
            'tiktok_url' => 'TikTok',
            'linkedin_url' => 'LinkedIn',
            'facebook_enabled' => 'estado de Facebook',
            'instagram_enabled' => 'estado de Instagram',
            'twitter_enabled' => 'estado de Twitter',
            'youtube_enabled' => 'estado de YouTube',
            'tiktok_enabled' => 'estado de TikTok',
            'linkedin_enabled' => 'estado de LinkedIn',
            'primary_color' => 'color primario',
            'secondary_color' => 'color secundario',
            'logo' => 'logotipo',
            'terms_conditions' => 'términos y condiciones',
            'privacy_policy' => 'política de privacidad',
            'claims_book_information' => 'información del libro de reclamaciones',
        ];
    }
}
