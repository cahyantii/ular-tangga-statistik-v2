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
                    'jumlah_petak' => 100,
                    'jumlah_kolom' => 10,
                    'thumbnail' => null,
                    'is_active' => true,
                ]
            );

            // Bersihkan petak & konektor lama papan ini agar seeder idempotent (aman dijalankan ulang)
            $papan->petak()->delete();
            $papan->papanKonektor()->delete();

            // Papan didominasi petak soal (mayoritas kotak) — hanya posisi berikut yang
            // dikecualikan dari default Soal: Start, Finish, awal Tangga/Ular, dan
            // sedikit Bonus/Penalti/Mystery sebagai variasi.
            $jenisPerPosisi = [
                1 => TileType::Start,
                6 => TileType::Tangga,
                14 => TileType::Tangga,
                17 => TileType::Ular,
                24 => TileType::Tangga,
                31 => TileType::Ular,
                33 => TileType::Tangga,
                40 => TileType::Bonus,
                45 => TileType::Ular,
                52 => TileType::Tangga,
                55 => TileType::Penalti,
                58 => TileType::Ular,
                61 => TileType::Tangga,
                68 => TileType::Ular,
                71 => TileType::Tangga,
                82 => TileType::Ular,
                88 => TileType::Tangga,
                90 => TileType::Mystery,
                94 => TileType::Ular,
                100 => TileType::Finish,
            ];

            $kategoriCycle = [$statistikaDasar->id, $pengenalanBps->id, $indikatorStatistik->id];
            $kategoriIndex = 0;

            $petakRows = [];
            for ($posisi = 1; $posisi <= 100; $posisi++) {
                $jenis = $jenisPerPosisi[$posisi] ?? TileType::Soal;
                $kategoriId = null;

                if ($jenis === TileType::Soal) {
                    $kategoriId = $kategoriCycle[$kategoriIndex % 3];
                    $kategoriIndex++;
                }

                $petakRows[] = [
                    'papan_id' => $papan->id,
                    'posisi' => $posisi,
                    'jenis_petak' => $jenis->value,
                    'kategori_id' => $kategoriId,
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
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 6, 'posisi_akhir' => 19, 'label' => 'Tangga Sensus'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 14, 'posisi_akhir' => 28, 'label' => 'Tangga IPM'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 24, 'posisi_akhir' => 37, 'label' => 'Tangga Registrasi'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 33, 'posisi_akhir' => 48, 'label' => 'Tangga Data Terbuka'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 52, 'posisi_akhir' => 66, 'label' => 'Tangga Digitalisasi'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 61, 'posisi_akhir' => 75, 'label' => 'Tangga Survei Cepat'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 71, 'posisi_akhir' => 85, 'label' => 'Tangga Big Data'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 88, 'posisi_akhir' => 97, 'label' => 'Tangga Satu Data'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 17, 'posisi_akhir' => 4, 'label' => 'Ular Data Tidak Valid'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 31, 'posisi_akhir' => 16, 'label' => 'Ular Sampel Bias'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 45, 'posisi_akhir' => 27, 'label' => 'Ular Outlier'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 58, 'posisi_akhir' => 41, 'label' => 'Ular Response Bias'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 68, 'posisi_akhir' => 50, 'label' => 'Ular Margin Error'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 82, 'posisi_akhir' => 63, 'label' => 'Ular Non-Respon'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 94, 'posisi_akhir' => 77, 'label' => 'Ular Duplikasi Data'],
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
