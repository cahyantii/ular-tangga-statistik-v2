<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori_materi,id'],
            'pertanyaan' => ['required', 'string'],
            'opsi_a' => ['required', 'string', 'max:500'],
            'opsi_b' => ['required', 'string', 'max:500'],
            'opsi_c' => ['required', 'string', 'max:500'],
            'opsi_d' => ['required', 'string', 'max:500'],
            'kunci_jawaban' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'pembahasan' => ['required', 'string'],
            'is_active' => ['boolean'],
        ];
    }
}
