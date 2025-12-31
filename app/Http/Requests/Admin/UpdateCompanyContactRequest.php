<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyContactRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'          => ['nullable', 'email', 'max:255'],
            'support_email'  => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:25'],
            'support_phone'  => ['nullable', 'string', 'max:25'],
            'address'        => ['nullable', 'string', 'max:255'],
            'website'        => ['nullable', 'url', 'max:255'],
        ];
    }
}
