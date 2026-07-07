<?php

namespace App\Services\Master;

use App\Enums\GameStatus;
use App\Enums\TileType;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Restrict semantics untuk Papan Permainan (Tahap 16, keputusan final):
 * tidak bisa dihapus/dinonaktifkan jika sedang dipakai sesi permainan aktif,
 * dan tidak bisa dihapus/dinonaktifkan jika ini papan aktif terakhir yang tersisa.
 */
class PapanPermainanService
{
    public function create(array $data): PapanPermainan
    {
        return DB::transaction(function () use ($data) {
            $papan = PapanPermainan::create($data);

            $now = now();
            $rows = [];

            for ($posisi = 1; $posisi <= $papan->jumlah_petak; $posisi++) {
                $jenis = match (true) {
                    $posisi === 1 => TileType::Start,
                    $posisi === $papan->jumlah_petak => TileType::Finish,
                    default => TileType::Biasa,
                };

                $rows[] = [
                    'papan_id' => $papan->id,
                    'posisi' => $posisi,
                    'jenis_petak' => $jenis->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            Petak::insert($rows);

            return $papan;
        });
    }

    public function delete(PapanPermainan $papan): void
    {
        $this->assertNotInActiveUse($papan);
        $this->assertNotLastActiveBoard($papan);

        $papan->delete();
    }

    public function deactivate(PapanPermainan $papan): void
    {
        $this->assertNotInActiveUse($papan);
        $this->assertNotLastActiveBoard($papan);

        $papan->update(['is_active' => false]);
    }

    public function activate(PapanPermainan $papan): void
    {
        $papan->update(['is_active' => true]);
    }

    private function assertNotInActiveUse(PapanPermainan $papan): void
    {
        $sedangDipakai = $papan->gameSessions()
            ->whereIn('status', [GameStatus::Playing->value, GameStatus::Paused->value])
            ->exists();

        if ($sedangDipakai) {
            throw ValidationException::withMessages([
                'papan' => "Papan \"{$papan->nama}\" sedang digunakan oleh sesi permainan aktif dan tidak bisa dihapus/dinonaktifkan.",
            ]);
        }
    }

    private function assertNotLastActiveBoard(PapanPermainan $papan): void
    {
        if (! $papan->is_active) {
            return;
        }

        $totalAktifLain = PapanPermainan::query()->active()->where('id', '!=', $papan->id)->count();

        if ($totalAktifLain === 0) {
            throw ValidationException::withMessages([
                'papan' => 'Tidak bisa menghapus/menonaktifkan papan aktif terakhir yang tersisa.',
            ]);
        }
    }
}
