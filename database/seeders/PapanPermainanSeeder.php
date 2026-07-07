<?php

namespace Database\Seeders;

use App\Enums\ConnectorType;
use App\Enums\TileType;
use App\Models\KategoriMateri;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PapanPermainanSeeder extends Seeder
{
    public function run(): void
    {
        $statistikaDasar = KategoriMateri::where('slug', 'statistika-dasar')->firstOrFail();
        $pengenalanBps = KategoriMateri::where('slug', 'pengenalan-bps')->firstOrFail();
        $indikatorStatistik = KategoriMateri::where('slug', 'indikator-statistik')->firstOrFail();

        DB::transaction(function () use ($statistikaDasar, $pengenalanBps, $indikatorStatistik) {
            $papan = PapanPermainan::updateOrCreate(
                ['nama' => 'Papan Statistik Indonesia'],
                [
                    'jumlah_petak' => 50,
                    'jumlah_kolom' => 10,
                    'thumbnail' => null,
                    'is_active' => true,
                ]
            );

            // Bersihkan petak & konektor lama papan ini agar seeder idempotent (aman dijalankan ulang)
            $papan->petak()->delete();
            $papan->papanKonektor()->delete();

            $kategoriUntukSoal = [
                3 => $statistikaDasar->id,
                7 => $pengenalanBps->id,
                12 => $indikatorStatistik->id,
                16 => $statistikaDasar->id,
                20 => $pengenalanBps->id,
                26 => $indikatorStatistik->id,
                32 => $statistikaDasar->id,
                36 => $pengenalanBps->id,
                41 => $indikatorStatistik->id,
                46 => $statistikaDasar->id,
                49 => $pengenalanBps->id,
            ];

            $jenisPerPosisi = [
                1 => TileType::Start,
                5 => TileType::Bonus,
                8 => TileType::Tangga,
                10 => TileType::Penalti,
                14 => TileType::Biasa,
                18 => TileType::Mystery,
                22 => TileType::Biasa,
                23 => TileType::Bonus,
                25 => TileType::Tangga,
                28 => TileType::Penalti,
                30 => TileType::Ular,
                33 => TileType::Biasa,
                34 => TileType::Mystery,
                38 => TileType::Biasa,
                39 => TileType::Bonus,
                43 => TileType::Penalti,
                44 => TileType::Ular,
                47 => TileType::Mystery,
                50 => TileType::Finish,
            ];

            $petakRows = [];
            for ($posisi = 1; $posisi <= 50; $posisi++) {
                $jenis = $jenisPerPosisi[$posisi]
                    ?? (isset($kategoriUntukSoal[$posisi]) ? TileType::Soal : TileType::Biasa);

                $petakRows[] = [
                    'papan_id' => $papan->id,
                    'posisi' => $posisi,
                    'jenis_petak' => $jenis->value,
                    'kategori_id' => $kategoriUntukSoal[$posisi] ?? null,
                    'label' => null,
                    'icon' => null,
                    'warna' => null,
                    'deskripsi' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Petak::insert($petakRows);

            $konektor = [
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 8, 'posisi_akhir' => 22, 'label' => 'Tangga Sensus'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 25, 'posisi_akhir' => 38, 'label' => 'Tangga IPM'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 30, 'posisi_akhir' => 14, 'label' => 'Ular Data Tidak Valid'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 44, 'posisi_akhir' => 33, 'label' => 'Ular Sampel Bias'],
            ];

            foreach ($konektor as $item) {
                PapanKonektor::create([
                    'papan_id' => $papan->id,
                    'jenis' => $item['jenis'],
                    'posisi_awal' => $item['posisi_awal'],
                    'posisi_akhir' => $item['posisi_akhir'],
                    'label' => $item['label'],
                ]);
            }
        });
    }
}
