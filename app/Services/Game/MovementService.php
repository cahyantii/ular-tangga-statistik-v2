<?php

namespace App\Services\Game;

use App\Models\GamePlayer;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;

/**
 * Aturan Finish: exact landing (Tahap 17, keputusan final). Jika dadu melebihi
 * sisa langkah ke petak terakhir, pion tidak bergerak sama sekali (giliran
 * tetap dianggap terpakai). Konektor (tangga/ular) diterapkan maksimal satu
 * kali per giliran — tidak ada chaining, karena validasi Tahap 16/9c sudah
 * menjamin posisi_akhir sebuah konektor tidak pernah menjadi posisi_awal
 * konektor lain.
 */
class MovementService
{
    /**
     * @return array{posisi_sebelum: int, posisi_sesudah: int, blocked: bool, konektor: ?PapanKonektor}
     */
    public function move(GamePlayer $gamePlayer, int $nilaiDadu, PapanPermainan $papan): array
    {
        $posisiSebelum = $gamePlayer->posisi_pion;
        $sisaLangkah = $papan->jumlah_petak - $posisiSebelum;

        if ($nilaiDadu > $sisaLangkah) {
            return [
                'posisi_sebelum' => $posisiSebelum,
                'posisi_sesudah' => $posisiSebelum,
                'blocked' => true,
                'konektor' => null,
            ];
        }

        $posisiSetelahLangkah = $posisiSebelum + $nilaiDadu;

        $konektor = PapanKonektor::query()
            ->where('papan_id', $papan->id)
            ->where('posisi_awal', $posisiSetelahLangkah)
            ->first();

        $posisiAkhir = $konektor?->posisi_akhir ?? $posisiSetelahLangkah;

        $gamePlayer->posisi_pion = $posisiAkhir;
        $gamePlayer->save();

        return [
            'posisi_sebelum' => $posisiSebelum,
            'posisi_sesudah' => $posisiAkhir,
            'blocked' => false,
            'konektor' => $konektor,
        ];
    }
}
