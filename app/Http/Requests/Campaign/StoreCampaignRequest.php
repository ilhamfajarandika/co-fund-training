<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role === 'creator';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'description' => 'required|string|min:20|max:50000',
            'target_amount' => 'required|numeric|min:100000',
            'deadline' => 'required|date|after_or_equal:' . now()->addDays(7)->toDateString(),
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'video_url' => [
                'nullable',
                'url',
                'regex:/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be|vimeo\.com)\/.+$/i'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul campaign wajib diisi.',
            'description.required' => 'Deskripsi wajib diisi.',
            'target_amount.min' => 'Minimal target Rp100.000',
            'deadline.after' => 'Deadline harus setelah hari ini.'
        ];
    }

    protected function prepareForValidation(): void
    {
        $images = $this->file('images');

        if (empty($images)) {
            $images = [];
            foreach ($this->allFiles() as $key => $file) {
                if (str_starts_with($key, 'images')) {
                    $images[] = $file;
                }
            }
        }

        if (!empty($images)) {
            $this->merge([
                'images' => $images,
            ]);
        }
    }
}