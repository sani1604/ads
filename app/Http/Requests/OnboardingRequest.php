<?php
// app/Http/Requests/OnboardingRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $step = $this->route('step', 1);

        return match($step) {
            1 => $this->stepOneRules(),
            2 => $this->stepTwoRules(),
            3 => $this->stepThreeRules(),
            4 => $this->stepFourRules(),
            default => [],
        };
    }

    protected function stepOneRules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'industry_id' => ['required', 'exists:industries,id'],
            'company_website' => ['nullable', 'url', 'max:255'],
        ];
    }

    protected function stepTwoRules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^\d{6}$/'],
            'gst_number' => ['nullable', 'string', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[A-Z\d]{1}[Z]{1}[A-Z\d]{1}$/'],
        ];
    }

    protected function stepThreeRules(): array
    {
        return [
            'business_goals' => ['required', 'array', 'min:1'],
            'business_goals.*' => ['string', 'in:leads,sales,brand_awareness,website_traffic,app_installs'],
            'monthly_budget' => ['required', 'string', 'in:10000-25000,25000-50000,50000-100000,100000+'],
            'target_audience' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function stepFourRules(): array
    {
        return [
            'facebook_page_url' => ['nullable', 'url'],
            'instagram_handle' => ['nullable', 'string', 'max:100'],
            'google_business_url' => ['nullable', 'url'],
            'existing_website' => ['nullable', 'url'],
        ];
    }

    public function messages(): array
    {
        return [
            'postal_code.regex' => 'Please enter a valid 6-digit PIN code.',
            'gst_number.regex' => 'Please enter a valid GST number.',
        ];
    }
}