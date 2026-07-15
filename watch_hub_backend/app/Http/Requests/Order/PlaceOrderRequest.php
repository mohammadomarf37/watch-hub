<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'in:cod,card,paypal'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'address_id.required' => 'Please select a delivery address.',
            'address_id.exists' => 'The selected address is invalid.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Payment method must be one of: COD, Card, or PayPal.',
        ];
    }
}
