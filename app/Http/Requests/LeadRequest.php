<?php
// app/Http/Requests/LeadRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'source' => ['required', 'in:facebook,instagram,google,linkedin,website,manual,other'],
            'campaign_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:new,contacted,qualified,converted,lost,spam'],
            'quality' => ['nullable', 'in:hot,warm,cold'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
        ];
    }
}