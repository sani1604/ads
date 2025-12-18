<?php
// app/Http/Requests/WalletRechargeRequest.php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class WalletRechargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $minAmount = Setting::get('min_wallet_recharge', 5000);

        return [
            'amount' => ['required', 'numeric', "min:{$minAmount}", 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        $minAmount = Setting::get('min_wallet_recharge', 5000);

        return [
            'amount.min' => "Minimum recharge amount is ₹{$minAmount}.",
            'amount.max' => 'Maximum recharge amount is ₹10,00,000.',
        ];
    }
}