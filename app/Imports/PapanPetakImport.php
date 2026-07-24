<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Kolom yang diharapkan (baris pertama = header, case-insensitive):
 * papan_id, papan_nama, posisi, jenis_petak, kategori, label, icon, warna, border_warna, deskripsi
 *
 * Validasi baris-per-baris dilakukan di PapanPetakImportService, bukan di
 * sini, agar bisa menghasilkan ringkasan valid/invalid sebelum commit.
 */
class PapanPetakImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    public function collection(Collection $rows): void
    {
        $this->rows = $rows;
    }
}
