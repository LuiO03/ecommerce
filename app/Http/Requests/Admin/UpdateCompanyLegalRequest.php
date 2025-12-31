<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyLegalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'terms_conditions' => ['required', 'string', 'min:20', 'max:12000'],
            'privacy_policy' => ['required', 'string', 'min:20', 'max:12000'],
            'claims_book_information' => ['required', 'string', 'min:10', 'max:8000'],
        ];
    }
}
