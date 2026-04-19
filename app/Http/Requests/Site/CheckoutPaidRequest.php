<?php

namespace App\Http\Requests\Site;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPaidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'transactionToken' => ['required', 'string', 'max:255'],
            'purchaseNumber' => ['required', 'string', 'max:30', 'regex:/^[0-9]+$/'],
            'delivery_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'address_id' => ['nullable', 'integer', 'min:1'],
            'store_id' => ['nullable', 'string', 'max:120'],
            'payment_method' => ['required', Rule::in(['niubiz', 'culqi', 'mercadopago', 'pagoefectivo', 'yape'])],
            'idempotency_key' => ['nullable', 'string', 'max:120'],
        ];
    }
}
