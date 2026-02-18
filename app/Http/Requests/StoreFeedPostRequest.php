<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFeedPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'legend' => ['required', 'string'],
            'level_importance' => ['required', 'in:0,1,2'],
            'anexo' => ['nullable', 'file', 'max:5120'],
        ];
    }
}
