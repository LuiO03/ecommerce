<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyMainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // General
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:1500'],
            // Contacto
            'email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:25'],
            'support_phone' => ['nullable', 'string', 'max:25'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            // Identidad visual
            'primary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'secondary_color' => ['nullable', 'regex:/^#(?:[0-9a-fA-F]{3}){1,2}$/'],
            'logo' => ['nullable', 'image', 'mimes:png', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
