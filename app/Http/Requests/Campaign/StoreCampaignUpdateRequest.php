<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'creator';
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'content' => 'required|string|min:10|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul update wajib diisi.',
            'content.required' => 'Konten update wajib diisi.',
            'content.min' => 'Konten update minimal 10 karakter.',
        ];
    }
}
