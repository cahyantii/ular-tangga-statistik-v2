<?php

namespace App\Services\Master;

use App\Imports\PapanPetakImport;
use App\Models\KategoriMateri;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Bulk-edit petak satu papan lewat Excel/CSV: hanya mengubah baris yang sudah
 * ada (tidak pernah membuat/menghapus petak, jumlah_petak tetap terkunci),
 * dan menolak baris Start/Finish/Tangga/Ular — sama seperti batasan
 * UpdatePetakRequest/PetakController::assertEditable pada form editor biasa.
 */
class PapanPetakImportService
{
    private const EDITABLE_JENIS = ['biasa', 'mystery'];

    public function parseAndValidate(PapanPermainan $papan, string $absolutePath): array
    {
        $import = new PapanPetakImport();
        Excel::import($import, $absolutePath);

        Excel::import($import, $absolutePath);

        $existingByPosisi = $papan->petak()->get()->keyBy('posisi');

        $valid = [];
        $invalid = [];

        foreach ($import->rows as $index => $row) {
            $rowNumber = $index + 2;
            $row = $row->toArray();
            $errors = [];

            $papanId = (int) ($row['papan_id'] ?? 0);
            if ($papanId !== $papan->id) {
                $errors[] = "Baris ini milik papan lain (papan_id={$papanId}).";
            }

            $posisi = (int) ($row['posisi'] ?? 0);
            /** @var \App\Models\Petak|null $existing */
            $existing = $existingByPosisi->get($posisi);

            if (! $existing) {
                $errors[] = "Posisi {$posisi} tidak ditemukan pada papan ini.";
            } elseif (in_array($existing->jenis_petak->value, ['start', 'finish', 'tangga', 'ular'], true)) {
                $errors[] = "Petak posisi {$posisi} berjenis {$existing->jenis_petak->value} dan dikelola lewat Editor Petak/Konektor, bukan import massal.";
            }

            $jenis = trim((string) ($row['jenis_petak'] ?? ''));
            if (! in_array($jenis, self::EDITABLE_JENIS, true)) {
                $errors[] = "jenis_petak \"{$jenis}\" tidak valid untuk import massal.";
            }

            $kategoriId = null;

            if ($errors !== []) {
                $invalid[] = ['row_number' => $rowNumber, 'raw' => $row, 'errors' => $errors];

                continue;
            }

            $valid[] = [
                'row_number' => $rowNumber,
                'posisi' => $posisi,
                'jenis_petak' => $jenis,
                'kategori_id' => $kategoriId,
                'label' => ($row['label'] ?? '') !== '' ? $row['label'] : null,
                'icon' => ($row['icon'] ?? '') !== '' ? $row['icon'] : null,
                'warna' => ($row['warna'] ?? '') !== '' ? $row['warna'] : null,
                'border_warna' => ($row['border_warna'] ?? '') !== '' ? $row['border_warna'] : null,
                'deskripsi' => ($row['deskripsi'] ?? '') !== '' ? $row['deskripsi'] : null,
            ];
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }

    public function commit(PapanPermainan $papan, array $validRows): int
    {
        return DB::transaction(function () use ($papan, $validRows) {
            $count = 0;

            foreach ($validRows as $row) {
                $updated = Petak::query()
                    ->where('papan_id', $papan->id)
                    ->where('posisi', $row['posisi'])
                    ->update([
                        'jenis_petak' => $row['jenis_petak'],
                        'kategori_id' => $row['kategori_id'],
                        'label' => $row['label'],
                        'icon' => $row['icon'],
                        'warna' => $row['warna'],
                        'border_warna' => $row['border_warna'],
                        'deskripsi' => $row['deskripsi'],
                    ]);

                $count += $updated;
            }

            return $count;
        });
    }
}
