<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'min_amount' => 'required|numeric|min:10000',
            'quota' => 'required|integer|min:1',
            'remaining_quota' => 'required|integer|min:0',
            'reward_description' => 'required|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama tier wajib diisi.',
            'min_amount.required' => 'Minimal amount wajib diisi.',
            'min_amount.min' => 'Minimal amount Rp10.000.',
            'quota.required' => 'Quota wajib diisi.',
            'quota.min' => 'Quota minimal 1.',
            'remaining_quota.required' => 'Remaining quota wajib diisi.',
            'remaining_quota.min' => 'Remaining quota tidak boleh negatif.',
            'reward_description.required' => 'Deskripsi reward wajib diisi.',
        ];
    }
}
