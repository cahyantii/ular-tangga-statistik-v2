<?php

namespace App\Http\Requests\Admin;

use App\Enums\ConnectorType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePapanKonektorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::enum(ConnectorType::class)],
            'posisi_awal' => ['required', 'integer', 'min:1'],
            'posisi_akhir' => ['required', 'integer', 'min:1', 'different:posisi_awal'],
            'label' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }
}
