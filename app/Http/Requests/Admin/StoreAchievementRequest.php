<?php

namespace App\Http\Requests\Admin;

use App\Enums\AchievementCriteriaType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAchievementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('achievements', 'kode')],
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'warna_badge' => ['nullable', 'string', 'max:255'],
            'syarat_type' => ['required', Rule::enum(AchievementCriteriaType::class)],
            'syarat_value' => ['required', 'integer', 'min:1'],
            'urutan' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
