<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'payment_method' => ['required', 'string'],
            'payment_method_id' => ['nullable', 'string'],
            'save_card' => ['nullable', 'boolean'],
        ];

        if ($this->payment_method === 'cod') {
            $rules['delivery_address'] = ['required', 'string', 'min:5', 'max:500'];
            $rules['delivery_phone'] = ['required', 'string', 'min:6', 'max:20'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Please select a payment method.',
            'delivery_address.required' => 'Delivery address is required for Cash on Delivery orders.',
            'delivery_phone.required' => 'Phone number is required for Cash on Delivery orders.',
            'delivery_address.min' => 'Please enter a complete delivery address.',
            'delivery_phone.min' => 'Please enter a valid phone number.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $paymentMethod = $this->payment_method;
            $validMethods = ['cod', 'kbz_pay', 'stripe'];

            if (str_starts_with($paymentMethod, 'saved_')) {
                // Valid - saved card
            } elseif (! in_array($paymentMethod, $validMethods)) {
                $validator->errors()->add('payment_method', 'Invalid payment method selected.');
            }
        });
    }
}
