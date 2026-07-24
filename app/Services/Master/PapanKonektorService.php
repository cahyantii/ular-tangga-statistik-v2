<?php

namespace App\Services\Master;

use App\Enums\ConnectorType;
use App\Enums\TileType;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Menjaga konsistensi antara `papan_konektor` dan `petak.jenis_petak` di petak
 * posisi_awal-nya, plus validasi no-chaining (Tahap 17, keputusan final):
 * posisi_akhir sebuah konektor tidak boleh sama dengan posisi_awal konektor
 * lain pada papan yang sama.
 */
class PapanKonektorService
{
    public function create(PapanPermainan $papan, array $data): PapanKonektor
    {
        $this->validate($papan, $data, null);

        return DB::transaction(function () use ($papan, $data) {
            $konektor = PapanKonektor::create([
                'papan_id' => $papan->id,
                'jenis' => $data['jenis'],
                'posisi_awal' => $data['posisi_awal'],
                'posisi_akhir' => $data['posisi_akhir'],
                'label' => $data['label'] ?? null,
                'icon' => $data['icon'] ?? null,
            ]);

            $this->syncPetak($papan, $konektor);

            return $konektor;
        });
    }

    public function update(PapanPermainan $papan, PapanKonektor $konektor, array $data): void
    {
        $this->validate($papan, $data, $konektor->id);

        DB::transaction(function () use ($papan, $konektor, $data) {
            $posisiAwalLama = $konektor->posisi_awal;

            $konektor->update([
                'jenis' => $data['jenis'],
                'posisi_awal' => $data['posisi_awal'],
                'posisi_akhir' => $data['posisi_akhir'],
                'label' => $data['label'] ?? null,
                'icon' => $data['icon'] ?? null,
            ]);

            if ($posisiAwalLama !== $konektor->posisi_awal) {
                $this->resetPetak($papan, $posisiAwalLama);
            }

            $this->syncPetak($papan, $konektor);
        });
    }

    public function delete(PapanPermainan $papan, PapanKonektor $konektor): void
    {
        DB::transaction(function () use ($papan, $konektor) {
            $posisiAwal = $konektor->posisi_awal;
            $konektor->delete();
            $this->resetPetak($papan, $posisiAwal);
        });
    }

    private function validate(PapanPermainan $papan, array $data, ?int $excludeId): void
    {
        $posisiAwal = (int) $data['posisi_awal'];
        $posisiAkhir = (int) $data['posisi_akhir'];
        $errors = [];

        if ($posisiAwal <= 1 || $posisiAwal >= $papan->jumlah_petak) {
            $errors[] = 'Posisi awal tidak boleh di petak Start atau Finish.';
        }

        if ($posisiAkhir <= 1 || $posisiAkhir >= $papan->jumlah_petak) {
            $errors[] = 'Posisi akhir tidak boleh di petak Start atau Finish.';
        }

        $jenis = (string) $data['jenis'];

        if ($jenis === ConnectorType::Tangga->value && $posisiAkhir <= $posisiAwal) {
            $errors[] = 'Tangga harus naik: posisi akhir harus lebih besar dari posisi awal.';
        }

        if ($jenis === ConnectorType::Ular->value && $posisiAkhir >= $posisiAwal) {
            $errors[] = 'Ular harus turun: posisi akhir harus lebih kecil dari posisi awal.';
        }

        $konektorLain = $papan->papanKonektor()
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->get();

        if ($konektorLain->contains('posisi_awal', $posisiAwal)) {
            $errors[] = "Sudah ada konektor lain yang dimulai dari posisi {$posisiAwal}.";
        }

        if ($konektorLain->contains('posisi_awal', $posisiAkhir)) {
            $errors[] = "Posisi akhir ({$posisiAkhir}) tidak boleh sama dengan posisi awal konektor lain (mencegah chaining).";
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['konektor' => $errors]);
        }
    }

    private function syncPetak(PapanPermainan $papan, PapanKonektor $konektor): void
    {
        Petak::query()
            ->where('papan_id', $papan->id)
            ->where('posisi', $konektor->posisi_awal)
            ->update([
                'jenis_petak' => $konektor->jenis->value,
                'label' => $konektor->label,
                'icon' => $konektor->icon,
            ]);
    }

    private function resetPetak(PapanPermainan $papan, int $posisi): void
    {
        Petak::query()
            ->where('papan_id', $papan->id)
            ->where('posisi', $posisi)
            ->update([
                'jenis_petak' => TileType::Biasa->value,
                'label' => null,
                'icon' => null,
            ]);
    }
}
