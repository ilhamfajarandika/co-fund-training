<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'min_amount' => 'sometimes|numeric|min:10000',
            'quota' => 'sometimes|integer|min:1',
            'remaining_quota' => 'sometimes|integer|min:0',
            'reward_description' => 'sometimes|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Nama tier maksimal 100 karakter.',
            'min_amount.min' => 'Minimal amount Rp10.000.',
            'quota.min' => 'Quota minimal 1.',
            'remaining_quota.min' => 'Remaining quota tidak boleh negatif.',
            'reward_description.max' => 'Deskripsi reward maksimal 5000 karakter.',
        ];
    }
}
