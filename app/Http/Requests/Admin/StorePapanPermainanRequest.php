<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePapanPermainanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jumlah_petak' => ['required', 'integer', 'min:10', 'max:200'],
            'jumlah_kolom' => ['required', 'integer', 'min:1', 'lte:jumlah_petak'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
