<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * "tangga"/"ular" sengaja tidak termasuk opsi jenis_petak yang bisa dipilih
 * langsung di sini — dua jenis itu hanya boleh dikelola lewat halaman Konektor
 * agar `petak.jenis_petak` selalu sinkron dengan baris `papan_konektor` terkait.
 * "start"/"finish" juga tidak termasuk karena posisinya tetap (petak pertama
 * dan terakhir papan) dan tidak bisa diubah jenisnya.
 */
class UpdatePetakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_petak' => ['required', Rule::in(['biasa', 'mystery'])],
            'is_active' => ['boolean'],
            'kategori_id' => ['nullable', 'integer', 'exists:kategori_materi,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'warna' => ['nullable', 'string', 'max:255'],
            'border_warna' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            '_version' => ['required', 'string'],
        ];
    }
}
