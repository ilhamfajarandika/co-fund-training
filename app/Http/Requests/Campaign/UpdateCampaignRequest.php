<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
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
            'title' => 'sometimes|string|max:100',
            'slug' => 'sometimes|string|max:100|unique:campaigns,slug,' . $this->route('campaign')->id,
            'description' => 'sometimes|string|min:20',
            'target_amount' => 'sometimes|numeric|min:100000',
            'deadline' => 'sometimes|date|after_or_equal:' . now()->addDays(7)->toDateString(),
        ];
    }

    public function messages(): array
    {
        return [
            'deadline.after' => 'Deadline harus setelah hari ini.'
        ];
    }
}
