<?php
// app/Http/Requests/SupportTicketRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    return [
        'subject' => ['required', 'string', 'max:255'],
        'category' => ['required', 'in:billing,technical,creative,leads,general'],
        'priority' => ['nullable', 'in:low,medium,high,urgent'],
        'message' => ['required', 'string', 'max:5000'],

        // Key part:
        'attachments'   => ['nullable', 'array', 'max:5'],
        'attachments.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,doc,docx', 'max:5120'],
    ];
}
}