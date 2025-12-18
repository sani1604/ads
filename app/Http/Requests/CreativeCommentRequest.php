<?php
// app/Http/Requests/CreativeCommentRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreativeCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:creative_comments,id'],
            'position' => ['nullable', 'array'],
            'position.x' => ['required_with:position', 'numeric', 'min:0'],
            'position.y' => ['required_with:position', 'numeric', 'min:0'],
        ];
    }
}