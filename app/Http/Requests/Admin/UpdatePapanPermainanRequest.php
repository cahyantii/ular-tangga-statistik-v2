<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * "jumlah_petak" sengaja tidak ada di sini — terkunci setelah papan dibuat
 * (Tahap 16, keputusan final). Mengubah ukuran berarti membuat papan baru.
 */
class UpdatePapanPermainanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'jumlah_kolom' => ['required', 'integer', 'min:1', 'lte:jumlah_petak_saat_ini'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            '_version' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'jumlah_petak_saat_ini' => $this->route('papan_permainan')?->jumlah_petak,
        ]);
    }
}
