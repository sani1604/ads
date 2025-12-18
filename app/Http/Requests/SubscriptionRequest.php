<?php
// app/Http/Requests/SubscriptionRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => ['required', 'exists:packages,id'],
            'payment_method' => ['required', 'in:razorpay,wallet'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}