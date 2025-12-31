<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyGeneralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'ruc' => ['nullable', 'regex:/^\d{11}$/'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:1500'],
        ];
    }
}
