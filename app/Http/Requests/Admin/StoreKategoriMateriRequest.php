<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreKategoriMateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('kategori_materi', 'slug')],
            'icon' => ['nullable', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
