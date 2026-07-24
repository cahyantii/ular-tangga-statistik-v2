<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Papan "Papan Statistik Indonesia" (lihat PapanPermainanSeeder) punya 5
 * pasang konektor tangga/ular yang jalurnya terbukti berpotongan secara
 * geometris saat digambar di grid 10 kolom (lihat
 * resources/js/board/BoardGeometry.js) - dihitung lewat pengecekan
 * perpotongan segmen garis, bukan sekadar dugaan visual:
 * 6->19 X 17->4, 52->66 X 68->50, 61->75 X 82->63, 71->85 X 94->77,
 * 88->97 X 94->77.
 *
 * Migrasi ini menggeser posisi_akhir (BUKAN posisi_awal - tile
 * tangga/ular itu sendiri tidak berubah, lihat $jenisPerPosisi di
 * PapanPermainanSeeder) ke titik pendaratan yang sudah diverifikasi
 * bebas dari perpotongan (nyaris semuanya jadi garis vertikal lurus,
 * hanya satu yang bergeser 1 kolom). PapanPermainanSeeder.php sudah
 * disinkronkan dengan nilai yang sama, migrasi ini HANYA memperbaiki
 * baris yang sudah terlanjur ter-seed di database yang sudah berjalan
 * (re-run seeder tidak dipakai di sini karena ia juga menghapus &
 * membuat ulang seluruh tabel petak papan ini, berisiko untuk sesi
 * permainan yang sedang berjalan/tersimpan).
 */
return new class extends Migration
{
    /**
     * [papan_konektor.posisi_awal => [posisi_akhir lama, posisi_akhir baru]]
     */
    private const REPOSITION = [
        6 => [19, 15],
        14 => [28, 27],
        31 => [16, 11],
        45 => [27, 25],
        52 => [66, 69],
        58 => [41, 43],
        61 => [75, 80],
        68 => [50, 49],
        71 => [85, 90],
        82 => [63, 62],
        88 => [97, 93],
        94 => [77, 74],
    ];

    public function up(): void
    {
        $this->applyMapping(fn (array $pair) => [$pair[0], $pair[1]]);
    }

    public function down(): void
    {
        $this->applyMapping(fn (array $pair) => [$pair[1], $pair[0]]);
    }

    /**
     * @param  callable(array{0:int,1:int}): array{0:int,1:int}  $direction
     *   Mengembalikan [posisi_akhir_saat_ini, posisi_akhir_tujuan] - dipakai
     *   sama untuk up() (lama->baru) dan down() (baru->lama).
     */
    private function applyMapping(callable $direction): void
    {
        $papanIds = DB::table('papan_permainan')
            ->where('nama', 'Papan Statistik Indonesia')
            ->pluck('id');

        if ($papanIds->isEmpty()) {
            return;
        }

        foreach (self::REPOSITION as $posisiAwal => $pair) {
            [$dari, $ke] = $direction($pair);

            DB::table('papan_konektor')
                ->whereIn('papan_id', $papanIds)
                ->where('posisi_awal', $posisiAwal)
                ->where('posisi_akhir', $dari)
                ->update(['posisi_akhir' => $ke]);
        }
    }
};
