<?php

namespace Database\Seeders;

use App\Models\KategoriMateri;
use App\Models\Soal;
use Illuminate\Database\Seeder;

class SoalSeeder extends Seeder
{
    public function run(): void
    {
        $statistikaDasar = KategoriMateri::where('slug', 'statistika-dasar')->firstOrFail();
        $pengenalanBps = KategoriMateri::where('slug', 'pengenalan-bps')->firstOrFail();
        $indikatorStatistik = KategoriMateri::where('slug', 'indikator-statistik')->firstOrFail();

        $soalStatistikaDasar = [
            [
                'pertanyaan' => 'Data ujian 5 siswa adalah 70, 80, 90, 60, 100. Berapakah nilai mean (rata-rata) dari data tersebut?',
                'opsi_jawaban' => ['A' => '70', 'B' => '75', 'C' => '80', 'D' => '85'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Mean dihitung dengan menjumlahkan semua nilai lalu membaginya dengan banyaknya data: (70+80+90+60+100)/5 = 400/5 = 80.',
            ],
            [
                'pertanyaan' => 'Data terurut: 3, 5, 7, 9, 11. Berapakah median dari data tersebut?',
                'opsi_jawaban' => ['A' => '5', 'B' => '7', 'C' => '9', 'D' => '11'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Karena banyaknya data ganjil (5 data), median adalah nilai tepat di tengah setelah diurutkan, yaitu 7.',
            ],
            [
                'pertanyaan' => 'Dalam sekumpulan data nilai ulangan: 7, 8, 8, 9, 8, 6, 8, berapakah modusnya?',
                'opsi_jawaban' => ['A' => '6', 'B' => '7', 'C' => '8', 'D' => '9'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Modus adalah nilai yang paling sering muncul. Angka 8 muncul 4 kali, lebih banyak dari angka lainnya.',
            ],
            [
                'pertanyaan' => 'Grafik apa yang paling cocok digunakan untuk menunjukkan tren inflasi bulanan selama satu tahun?',
                'opsi_jawaban' => ['A' => 'Diagram lingkaran', 'B' => 'Grafik garis', 'C' => 'Grafik batang', 'D' => 'Tabel frekuensi'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Grafik garis paling cocok untuk menunjukkan perubahan/tren suatu nilai dari waktu ke waktu, seperti inflasi bulanan.',
            ],
            [
                'pertanyaan' => 'Manakah pernyataan yang benar mengenai perbedaan statistik dan statistika?',
                'opsi_jawaban' => [
                    'A' => 'Statistik adalah ilmunya, statistika adalah datanya',
                    'B' => 'Statistik adalah datanya, statistika adalah ilmunya',
                    'C' => 'Keduanya memiliki arti yang sama persis',
                    'D' => 'Statistik hanya dipakai di bidang ekonomi',
                ],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Statistik merujuk pada kumpulan data/angka, sedangkan statistika adalah ilmu yang mempelajari cara mengolah data tersebut.',
            ],
            [
                'pertanyaan' => 'Cabang statistika yang berfokus pada penarikan kesimpulan tentang populasi berdasarkan sampel disebut...',
                'opsi_jawaban' => ['A' => 'Statistika deskriptif', 'B' => 'Statistika inferensia', 'C' => 'Statistika terapan', 'D' => 'Statistika non-parametrik'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Statistika inferensia digunakan untuk menarik kesimpulan atau membuat prediksi tentang populasi berdasarkan data sampel.',
            ],
        ];

        $soalPengenalanBps = [
            [
                'pertanyaan' => 'Apa kepanjangan dari BPS?',
                'opsi_jawaban' => ['A' => 'Badan Pusat Statistik', 'B' => 'Biro Pusat Survei', 'C' => 'Badan Perencanaan Statistik', 'D' => 'Badan Pengelola Sensus'],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'BPS adalah singkatan dari Badan Pusat Statistik, lembaga pemerintah non-kementerian yang menyelenggarakan statistik nasional.',
            ],
            [
                'pertanyaan' => 'Setiap tanggal berapa Hari Statistik Nasional diperingati di Indonesia?',
                'opsi_jawaban' => ['A' => '17 Agustus', 'B' => '26 September', 'C' => '10 November', 'D' => '1 Januari'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Hari Statistik Nasional diperingati setiap tanggal 26 September untuk mengenang lahirnya lembaga statistik resmi di Indonesia.',
            ],
            [
                'pertanyaan' => 'Sensus Penduduk di Indonesia dilaksanakan setiap berapa tahun sekali?',
                'opsi_jawaban' => ['A' => '5 tahun', 'B' => '10 tahun', 'C' => '15 tahun', 'D' => '20 tahun'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Sensus Penduduk dilaksanakan setiap 10 tahun sekali, pada tahun yang berakhiran angka 0 (misalnya 2020, 2030).',
            ],
            [
                'pertanyaan' => 'Sensus Ekonomi dilaksanakan pada tahun yang berakhiran angka berapa?',
                'opsi_jawaban' => ['A' => '0', 'B' => '3', 'C' => '6', 'D' => '9'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Sensus Ekonomi dilaksanakan setiap 10 tahun pada tahun berakhiran angka 6, misalnya 2016 dan 2026.',
            ],
            [
                'pertanyaan' => 'Survei BPS yang digunakan untuk mengukur kondisi ketenagakerjaan disebut...',
                'opsi_jawaban' => ['A' => 'Susenas', 'B' => 'Sakernas', 'C' => 'Podes', 'D' => 'SUPAS'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Sakernas (Survei Angkatan Kerja Nasional) digunakan BPS untuk mengukur kondisi ketenagakerjaan, termasuk tingkat pengangguran.',
            ],
            [
                'pertanyaan' => 'Apa perbedaan utama antara sensus dan survei?',
                'opsi_jawaban' => [
                    'A' => 'Sensus mendata seluruh populasi, survei menggunakan sampel',
                    'B' => 'Survei mendata seluruh populasi, sensus menggunakan sampel',
                    'C' => 'Keduanya selalu mendata seluruh populasi',
                    'D' => 'Tidak ada perbedaan, istilah yang sama',
                ],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'Sensus mendata seluruh unit populasi, sedangkan survei menggunakan sampel yang mewakili populasi agar lebih efisien.',
            ],
        ];

        $soalIndikatorStatistik = [
            [
                'pertanyaan' => 'Apa kepanjangan dari IPM?',
                'opsi_jawaban' => ['A' => 'Indeks Pertumbuhan Masyarakat', 'B' => 'Indeks Pembangunan Manusia', 'C' => 'Indikator Produksi Masyarakat', 'D' => 'Indeks Perekonomian Modern'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'IPM adalah singkatan dari Indeks Pembangunan Manusia (Human Development Index).',
            ],
            [
                'pertanyaan' => 'Manakah yang BUKAN merupakan dimensi penyusun IPM?',
                'opsi_jawaban' => ['A' => 'Umur panjang dan hidup sehat', 'B' => 'Pengetahuan', 'C' => 'Standar hidup layak', 'D' => 'Jumlah penduduk'],
                'kunci_jawaban' => 'D',
                'pembahasan' => 'IPM disusun dari tiga dimensi: umur panjang dan hidup sehat, pengetahuan, dan standar hidup layak. Jumlah penduduk bukan salah satu dimensinya.',
            ],
            [
                'pertanyaan' => 'Apa kepanjangan dari PDRB?',
                'opsi_jawaban' => ['A' => 'Produk Domestik Regional Bruto', 'B' => 'Pendapatan Daerah Regional Bersih', 'C' => 'Produksi Dalam Rangka Bisnis', 'D' => 'Perhitungan Data Regional Bersama'],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'PDRB adalah singkatan dari Produk Domestik Regional Bruto, nilai total barang dan jasa yang dihasilkan di suatu wilayah.',
            ],
            [
                'pertanyaan' => 'Tingkat Pengangguran Terbuka (TPT) mengukur persentase pengangguran terhadap...',
                'opsi_jawaban' => ['A' => 'Jumlah penduduk', 'B' => 'Angkatan kerja', 'C' => 'Jumlah rumah tangga', 'D' => 'Penduduk usia sekolah'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'TPT adalah persentase jumlah pengangguran terhadap total angkatan kerja (penduduk usia kerja yang bekerja atau mencari kerja).',
            ],
            [
                'pertanyaan' => 'Apa yang dimaksud dengan inflasi?',
                'opsi_jawaban' => [
                    'A' => 'Penurunan harga barang secara umum dan terus-menerus',
                    'B' => 'Kenaikan harga barang secara umum dan terus-menerus',
                    'C' => 'Kenaikan jumlah penduduk secara drastis',
                    'D' => 'Penurunan jumlah pengangguran',
                ],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Inflasi adalah kecenderungan kenaikan harga barang dan jasa secara umum dan terus-menerus dalam periode tertentu.',
            ],
            [
                'pertanyaan' => 'BPS mengukur inflasi berdasarkan perubahan indeks apa?',
                'opsi_jawaban' => ['A' => 'Indeks Pembangunan Manusia', 'B' => 'Indeks Harga Konsumen', 'C' => 'Indeks Ketimpangan Gini', 'D' => 'Indeks Produksi Industri'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'BPS mengukur inflasi melalui Indeks Harga Konsumen (IHK) yang memantau perubahan harga barang/jasa yang dikonsumsi rumah tangga.',
            ],
        ];

        $this->createSoal($statistikaDasar->id, $soalStatistikaDasar);
        $this->createSoal($pengenalanBps->id, $soalPengenalanBps);
        $this->createSoal($indikatorStatistik->id, $soalIndikatorStatistik);
    }

    /**
     * @param  array<int, array<string, mixed>>  $daftarSoal
     */
    private function createSoal(int $kategoriId, array $daftarSoal): void
    {
        foreach ($daftarSoal as $soal) {
            Soal::updateOrCreate(
                ['kategori_id' => $kategoriId, 'pertanyaan' => $soal['pertanyaan']],
                [
                    'opsi_jawaban' => $soal['opsi_jawaban'],
                    'kunci_jawaban' => $soal['kunci_jawaban'],
                    'pembahasan' => $soal['pembahasan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
