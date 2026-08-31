<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class ApproveCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'admin';
    }

    public function rules(): array
    {
        return [
            'rejection_note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_note.max' => 'Catatan penolakan maksimal 1000 karakter.',
        ];
    }
}
