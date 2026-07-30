<?php

namespace App\Services\Master;

use App\Enums\ConnectorType;
use App\Http\Requests\Admin\StorePapanPermainanRequest;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Import Papan dari JSON: satu file = satu papan lengkap (papan + petak + konektor),
 * SELALU membuat papan baru (tidak pernah menimpa papan yang sudah ada), supaya
 * aturan "jumlah_petak terkunci setelah papan dibuat" tidak pernah bertabrakan
 * dengan alur import. Dua tahap seperti import Soal: parse+validasi dulu untuk
 * ringkasan sebelum admin konfirmasi, baru commit ke database.
 */
class PapanImportService
{
    private const VALID_JENIS = ['start', 'finish', 'biasa', 'mystery', 'tangga', 'ular'];

    public function parseAndValidate(string $absoluteJsonPath): array
    {
        $decoded = json_decode((string) file_get_contents($absoluteJsonPath), true) ?? [];

        $papanData = $decoded['papan'] ?? [];
        $papanValidator = Validator::make($papanData, (new StorePapanPermainanRequest())->rules());
        $papanErrors = $papanValidator->fails() ? $papanValidator->errors()->all() : [];

        $jumlahPetak = (int) ($papanData['jumlah_petak'] ?? 0);
        $petakRows = $decoded['petak'] ?? [];
        $petakErrors = $this->validatePetakRows($petakRows, $jumlahPetak);

        [$konektorValid, $konektorInvalid] = $this->splitKonektorRows($decoded['konektor'] ?? [], $jumlahPetak);

        return [
            'papan' => $papanData,
            'papan_errors' => $papanErrors,
            'petak' => $petakRows,
            'petak_errors' => $petakErrors,
            'konektor_valid' => $konektorValid,
            'konektor_invalid' => $konektorInvalid,
        ];
    }

    public function commit(array $parsed): PapanPermainan
    {
        return DB::transaction(function () use ($parsed) {
            $papanData = $parsed['papan'];

            $papan = PapanPermainan::create([
                'nama' => $papanData['nama'],
                'deskripsi' => $papanData['deskripsi'] ?? null,
                'jumlah_petak' => $papanData['jumlah_petak'],
                'jumlah_kolom' => $papanData['jumlah_kolom'],
                'thumbnail' => $papanData['thumbnail'] ?? null,
                'is_active' => $papanData['is_active'] ?? true,
            ]);

            $now = now();
            $rows = [];

            foreach ($parsed['petak'] as $row) {
                $rows[] = [
                    'papan_id' => $papan->id,
                    'posisi' => $row['posisi'],
                    'jenis_petak' => $row['jenis_petak'],
                    'kategori_id' => null,
                    'label' => $row['label'] ?? null,
                    'icon' => $row['icon'] ?? null,
                    'warna' => $row['warna'] ?? null,
                    'border_warna' => $row['border_warna'] ?? null,
                    'deskripsi' => $row['deskripsi'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Petak::insert($rows);

            $konektorService = app(PapanKonektorService::class);
            foreach ($parsed['konektor_valid'] as $row) {
                $konektorService->create($papan, [
                    'jenis' => $row['jenis'],
                    'posisi_awal' => $row['posisi_awal'],
                    'posisi_akhir' => $row['posisi_akhir'],
                    'label' => $row['label'] ?? null,
                    'icon' => $row['icon'] ?? null,
                ]);
            }

            return $papan;
        });
    }

    private function validatePetakRows(array $petakRows, int $jumlahPetak): array
    {
        $errors = [];

        if (count($petakRows) !== $jumlahPetak) {
            $errors[] = 'Jumlah baris petak ('.count($petakRows).") tidak sama dengan jumlah_petak papan ({$jumlahPetak}).";

            return $errors;
        }

        $posisiSeen = [];

        foreach ($petakRows as $index => $row) {
            $posisi = (int) ($row['posisi'] ?? 0);
            $jenis = $row['jenis_petak'] ?? null;

            if ($posisi < 1 || $posisi > $jumlahPetak) {
                $errors[] = "Baris petak #{$index}: posisi {$posisi} di luar rentang 1..{$jumlahPetak}.";

                continue;
            }

            if (isset($posisiSeen[$posisi])) {
                $errors[] = "Posisi petak {$posisi} duplikat.";
            }
            $posisiSeen[$posisi] = true;

            if (! in_array($jenis, self::VALID_JENIS, true)) {
                $errors[] = "Baris petak posisi {$posisi}: jenis_petak \"{$jenis}\" tidak valid.";
            }

            if ($posisi === 1 && $jenis !== 'start') {
                $errors[] = 'Posisi 1 harus berjenis start.';
            }

            if ($posisi === $jumlahPetak && $jenis !== 'finish') {
                $errors[] = "Posisi {$jumlahPetak} harus berjenis finish.";
            }
        }

        if (count($posisiSeen) !== $jumlahPetak) {
            $errors[] = "Tidak semua posisi 1..{$jumlahPetak} terisi.";
        }

        return $errors;
    }

    private function splitKonektorRows(array $konektorRows, int $jumlahPetak): array
    {
        $valid = [];
        $invalid = [];
        $accepted = [];

        foreach ($konektorRows as $index => $row) {
            $errors = $this->validateKonektorRow($row, $jumlahPetak, $accepted);

            if ($errors === []) {
                $valid[] = $row;
                $accepted[] = $row;
            } else {
                $invalid[] = ['row_number' => $index + 1, 'raw' => $row, 'errors' => $errors];
            }
        }

        return [$valid, $invalid];
    }

    private function validateKonektorRow(array $row, int $jumlahPetak, array $accepted): array
    {
        $errors = [];
        $jenis = $row['jenis'] ?? null;
        $awal = (int) ($row['posisi_awal'] ?? 0);
        $akhir = (int) ($row['posisi_akhir'] ?? 0);

        if (! in_array($jenis, [ConnectorType::Tangga->value, ConnectorType::Ular->value], true)) {
            $errors[] = 'Jenis konektor harus tangga atau ular.';
        }

        if ($awal <= 1 || $awal >= $jumlahPetak) {
            $errors[] = 'Posisi awal tidak boleh di petak Start atau Finish.';
        }

        if ($akhir <= 1 || $akhir >= $jumlahPetak) {
            $errors[] = 'Posisi akhir tidak boleh di petak Start atau Finish.';
        }

        if ($jenis === ConnectorType::Tangga->value && $akhir <= $awal) {
            $errors[] = 'Tangga harus naik: posisi akhir harus lebih besar dari posisi awal.';
        }

        if ($jenis === ConnectorType::Ular->value && $akhir >= $awal) {
            $errors[] = 'Ular harus turun: posisi akhir harus lebih kecil dari posisi awal.';
        }

        foreach ($accepted as $existing) {
            $existingAwal = (int) ($existing['posisi_awal'] ?? 0);

            if ($existingAwal === $awal) {
                $errors[] = "Sudah ada konektor lain yang dimulai dari posisi {$awal}.";
            }

            if ($existingAwal === $akhir) {
                $errors[] = "Posisi akhir ({$akhir}) tidak boleh sama dengan posisi awal konektor lain (mencegah chaining).";
            }
        }

        return $errors;
    }
}
