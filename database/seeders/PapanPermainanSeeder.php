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

            // Posisi tetap: Start/Finish serta petak asal setiap konektor Tangga/Ular
            // (jenisnya disinkronkan otomatis oleh PapanKonektorService lewat
            // PapanKonektor::create() di bawah — di sini hanya perlu tahu posisinya
            // supaya tidak ikut ditimpa oleh pola pengisi di bawah), plus segelintir
            // Bonus/Penalti/Mystery sebagai variasi tambahan di luar pola berulang.
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

            // Sisa posisi (di luar $jenisPerPosisi) diisi lewat pola berulang, BUKAN
            // default Soal seperti sebelumnya — supaya papan tidak didominasi petak
            // Soal (dulu 80 dari 100 posisi menjadi Soal, jauh lebih banyak dari
            // kebutuhan wajar dibanding jumlah soal aktif per kategori). Pola 16-slot
            // ini menghasilkan sekitar 50% Biasa, 25% Soal, sisanya Bonus/Penalti/
            // Mystery — komposisi papan yang jauh lebih bervariasi dan realistis.
            $fillerPattern = [
                TileType::Biasa, TileType::Soal, TileType::Biasa, TileType::Penalti,
                TileType::Biasa, TileType::Soal, TileType::Bonus, TileType::Biasa,
                TileType::Biasa, TileType::Soal, TileType::Biasa, TileType::Mystery,
                TileType::Biasa, TileType::Soal, TileType::Bonus, TileType::Biasa,
            ];

            $kategoriCycle = [$statistikaDasar->id, $pengenalanBps->id, $indikatorStatistik->id];
            $kategoriIndex = 0;
            $fillerIndex = 0;

            $petakRows = [];
            for ($posisi = 1; $posisi <= 100; $posisi++) {
                if (isset($jenisPerPosisi[$posisi])) {
                    $jenis = $jenisPerPosisi[$posisi];
                } else {
                    $jenis = $fillerPattern[$fillerIndex % count($fillerPattern)];
                    $fillerIndex++;
                }

                $kategoriId = null;

                if ($jenis === TileType::Soal) {
                    $kategoriId = $kategoriCycle[$kategoriIndex % 3];
                    $kategoriIndex++;
                }

                $petakRows[] = [
                    'papan_id' => $papan->id,
                    'posisi' => $posisi,
                    'jenis_petak' => $jenis->value,
                    'is_active' => true,
                    'kategori_id' => $kategoriId,
                    'label' => null,
                    'icon' => null,
                    'warna' => null,
                    'border_warna' => null,
                    'deskripsi' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Petak::insert($petakRows);

            // Posisi posisi_akhir di bawah ini sengaja dipilih agar SETIAP
            // konektor tergambar (nyaris) lurus vertikal pada grid 10 kolom
            // (lihat resources/js/board/BoardGeometry.js) - posisi_awal tidak
            // diubah (masih tile tangga/ular yang sama seperti sebelumnya,
            // konsisten dengan $jenisPerPosisi di atas), hanya titik
            // pendaratannya digeser supaya tidak saling menyilang secara
            // visual. Sebelumnya ada 5 pasang konektor yang jalurnya
            // terbukti berpotongan secara geometris (dihitung lewat
            // pengecekan perpotongan segmen garis): 6->19 X 17->4,
            // 52->66 X 68->50, 61->75 X 82->63, 71->85 X 94->77, dan
            // 88->97 X 94->77.
            $konektor = [
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 6, 'posisi_akhir' => 15, 'label' => 'Tangga Sensus'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 14, 'posisi_akhir' => 27, 'label' => 'Tangga IPM'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 24, 'posisi_akhir' => 37, 'label' => 'Tangga Registrasi'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 33, 'posisi_akhir' => 48, 'label' => 'Tangga Data Terbuka'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 52, 'posisi_akhir' => 69, 'label' => 'Tangga Digitalisasi'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 61, 'posisi_akhir' => 80, 'label' => 'Tangga Survei Cepat'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 71, 'posisi_akhir' => 90, 'label' => 'Tangga Big Data'],
                ['jenis' => ConnectorType::Tangga, 'posisi_awal' => 88, 'posisi_akhir' => 93, 'label' => 'Tangga Satu Data'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 17, 'posisi_akhir' => 4, 'label' => 'Ular Data Tidak Valid'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 31, 'posisi_akhir' => 11, 'label' => 'Ular Sampel Bias'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 45, 'posisi_akhir' => 25, 'label' => 'Ular Outlier'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 58, 'posisi_akhir' => 43, 'label' => 'Ular Response Bias'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 68, 'posisi_akhir' => 49, 'label' => 'Ular Margin Error'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 82, 'posisi_akhir' => 62, 'label' => 'Ular Non-Respon'],
                ['jenis' => ConnectorType::Ular, 'posisi_awal' => 94, 'posisi_akhir' => 74, 'label' => 'Ular Duplikasi Data'],
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
