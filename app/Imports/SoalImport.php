<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Kolom yang diharapkan (baris pertama = header, case-insensitive):
 * kategori, pertanyaan, opsi_a, opsi_b, opsi_c, opsi_d, kunci_jawaban, pembahasan, is_active
 *
 * Validasi baris-per-baris dilakukan di SoalImportService, bukan di sini,
 * agar bisa menghasilkan ringkasan valid/invalid sebelum commit ke database
 * (Tahap 16, keputusan final).
 */
class SoalImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
