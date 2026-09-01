<?php

namespace App\Http\Requests\Backing;

use Illuminate\Foundation\Http\FormRequest;

class StoreBackingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:10000',
            'tier_id' => 'nullable|exists:campaign_tiers,id',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimal donasi Rp10.000.',
            'tier_id.exists' => 'Tier tidak valid.',
        ];
    }
}
