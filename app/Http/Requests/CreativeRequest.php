<?php
// app/Http/Requests/CreativeRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreativeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:image,video,carousel,story,reel,document'],
            'platform' => ['required', 'in:facebook,instagram,google,linkedin,twitter,youtube,all'],
            'service_category_id' => ['nullable', 'exists:service_categories,id'],
            'ad_copy' => ['nullable', 'string', 'max:2000'],
            'cta_text' => ['nullable', 'string', 'max:50'],
            'landing_url' => ['nullable', 'url', 'max:500'],
            'scheduled_date' => ['nullable', 'date', 'after:today'],
        ];

        // Add file validation for create
        if ($this->isMethod('POST')) {
            $rules['files'] = ['required', 'array', 'min:1', 'max:10'];
            $rules['files.*'] = ['file', 'mimes:jpg,jpeg,png,gif,mp4,mov,avi,pdf', 'max:51200']; // 50MB
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Please upload at least one file.',
            'files.*.max' => 'Each file must not exceed 50MB.',
            'files.*.mimes' => 'Allowed file types: JPG, PNG, GIF, MP4, MOV, AVI, PDF.',
        ];
    }
}