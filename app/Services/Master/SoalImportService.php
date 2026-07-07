<?php

namespace App\Services\Master;

use App\Imports\SoalImport;
use App\Models\KategoriMateri;
use App\Models\Soal;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Alur import Soal dua tahap (Tahap 16, keputusan final): parse+validasi dulu
 * (tanpa commit) untuk menampilkan ringkasan baris valid/invalid ke admin,
 * baru commit ke database setelah admin konfirmasi. Mendukung .csv dan .xlsx.
 */
class SoalImportService
{
    private const REQUIRED_FIELDS = ['pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban', 'pembahasan'];

    public function parseAndValidate(string $absolutePath): array
    {
        $import = new SoalImport();
        Excel::import($import, $absolutePath);

        $kategoriByNama = KategoriMateri::query()->active()->get()->keyBy(fn ($k) => strtolower($k->nama));

        $valid = [];
        $invalid = [];

        foreach ($import->rows as $index => $row) {
            $rowNumber = $index + 2; // baris 1 = header
            $row = $row->toArray();
            $errors = [];

            $kategoriNama = trim((string) ($row['kategori'] ?? ''));
            $kategori = $kategoriByNama->get(strtolower($kategoriNama));

            if ($kategoriNama === '') {
                $errors[] = 'Kolom "kategori" wajib diisi.';
            } elseif (! $kategori) {
                $errors[] = "Kategori \"{$kategoriNama}\" tidak ditemukan atau tidak aktif.";
            }

            foreach (self::REQUIRED_FIELDS as $field) {
                if (trim((string) ($row[$field] ?? '')) === '') {
                    $errors[] = "Kolom \"{$field}\" wajib diisi.";
                }
            }

            $kunci = strtoupper(trim((string) ($row['kunci_jawaban'] ?? '')));
            if ($kunci !== '' && ! in_array($kunci, ['A', 'B', 'C', 'D'], true)) {
                $errors[] = 'Kolom "kunci_jawaban" harus salah satu dari A, B, C, atau D.';
            }

            if ($errors !== []) {
                $invalid[] = ['row_number' => $rowNumber, 'raw' => $row, 'errors' => $errors];

                continue;
            }

            $valid[] = [
                'row_number' => $rowNumber,
                'kategori_id' => $kategori->id,
                'kategori_nama' => $kategori->nama,
                'pertanyaan' => (string) $row['pertanyaan'],
                'opsi_jawaban' => [
                    'A' => (string) $row['opsi_a'],
                    'B' => (string) $row['opsi_b'],
                    'C' => (string) $row['opsi_c'],
                    'D' => (string) $row['opsi_d'],
                ],
                'kunci_jawaban' => $kunci,
                'pembahasan' => (string) $row['pembahasan'],
                'is_active' => $this->parseBoolean($row['is_active'] ?? true),
            ];
        }

        return ['valid' => $valid, 'invalid' => $invalid];
    }

    public function commit(array $validRows): int
    {
        return DB::transaction(function () use ($validRows) {
            foreach ($validRows as $row) {
                Soal::create([
                    'kategori_id' => $row['kategori_id'],
                    'pertanyaan' => $row['pertanyaan'],
                    'opsi_jawaban' => $row['opsi_jawaban'],
                    'kunci_jawaban' => $row['kunci_jawaban'],
                    'pembahasan' => $row['pembahasan'],
                    'is_active' => $row['is_active'],
                ]);
            }

            return count($validRows);
        });
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower(trim((string) $value));

        return ! in_array($normalized, ['0', 'false', 'tidak', 'no'], true);
    }
}
