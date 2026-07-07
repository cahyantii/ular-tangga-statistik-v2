<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MateriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kategori_id' => ['required', 'integer', 'exists:kategori_materi,id'],
            'judul' => ['required', 'string', 'max:255'],
            'konten' => ['required', 'string'],
            'urutan' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
