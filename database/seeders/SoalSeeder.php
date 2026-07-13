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
            [
                'pertanyaan' => 'Data nilai ujian: 60, 65, 70, 75, 90. Berapakah range (jangkauan) dari data tersebut?',
                'opsi_jawaban' => ['A' => '20', 'B' => '25', 'C' => '30', 'D' => '90'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Range dihitung dari nilai maksimum dikurangi nilai minimum: 90 - 60 = 30.',
            ],
            [
                'pertanyaan' => 'Ukuran yang menunjukkan seberapa jauh sebaran data terhadap nilai rata-ratanya disebut...',
                'opsi_jawaban' => ['A' => 'Modus', 'B' => 'Median', 'C' => 'Standar deviasi', 'D' => 'Frekuensi'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Standar deviasi mengukur sebaran/variasi data terhadap nilai rata-rata (mean); semakin besar nilainya, semakin tersebar datanya.',
            ],
            [
                'pertanyaan' => 'Data seperti "warna favorit" dan "jenis kelamin" termasuk jenis data...',
                'opsi_jawaban' => ['A' => 'Kuantitatif diskrit', 'B' => 'Kuantitatif kontinu', 'C' => 'Kualitatif', 'D' => 'Data time series'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Data kualitatif berupa kategori/label (bukan angka), seperti warna favorit atau jenis kelamin.',
            ],
            [
                'pertanyaan' => 'Sebagian kecil anggota populasi yang diambil untuk mewakili keseluruhan populasi disebut...',
                'opsi_jawaban' => ['A' => 'Sensus', 'B' => 'Sampel', 'C' => 'Parameter', 'D' => 'Variabel'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Sampel adalah sebagian anggota populasi yang dipilih untuk mewakili karakteristik keseluruhan populasi.',
            ],
            [
                'pertanyaan' => 'Diagram lingkaran (pie chart) paling cocok digunakan untuk menampilkan...',
                'opsi_jawaban' => ['A' => 'Tren data dari waktu ke waktu', 'B' => 'Proporsi/persentase tiap kategori terhadap keseluruhan', 'C' => 'Hubungan dua variabel numerik', 'D' => 'Urutan data dari terkecil ke terbesar'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Diagram lingkaran menunjukkan proporsi atau persentase setiap kategori terhadap total keseluruhan data.',
            ],
            [
                'pertanyaan' => 'Nilai yang membagi data terurut menjadi empat bagian sama besar disebut...',
                'opsi_jawaban' => ['A' => 'Persentil', 'B' => 'Kuartil', 'C' => 'Desil', 'D' => 'Modus'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Kuartil membagi data yang sudah terurut menjadi empat bagian sama besar (Q1, Q2/median, dan Q3).',
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
            [
                'pertanyaan' => 'Apa status kelembagaan BPS di pemerintahan Indonesia?',
                'opsi_jawaban' => ['A' => 'Kementerian', 'B' => 'Lembaga Pemerintah Non-Kementerian (LPNK)', 'C' => 'Badan Usaha Milik Negara', 'D' => 'Lembaga Swadaya Masyarakat'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'BPS adalah Lembaga Pemerintah Non-Kementerian (LPNK) yang bertanggung jawab langsung kepada Presiden.',
            ],
            [
                'pertanyaan' => 'Pendataan Potensi Desa yang dilakukan BPS untuk mengetahui potensi wilayah administrasi terkecil disebut...',
                'opsi_jawaban' => ['A' => 'Susenas', 'B' => 'Sakernas', 'C' => 'Podes', 'D' => 'SUPAS'],
                'kunci_jawaban' => 'C',
                'pembahasan' => 'Podes (Pendataan Potensi Desa) mengumpulkan data potensi desa/kelurahan di seluruh Indonesia.',
            ],
            [
                'pertanyaan' => 'Survei Sosial Ekonomi Nasional yang mengukur kesejahteraan rumah tangga dikenal dengan singkatan...',
                'opsi_jawaban' => ['A' => 'Susenas', 'B' => 'Sakernas', 'C' => 'SPTN', 'D' => 'ST'],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'Susenas (Survei Sosial Ekonomi Nasional) digunakan untuk mengukur tingkat kesejahteraan rumah tangga.',
            ],
            [
                'pertanyaan' => 'Sensus Penduduk Indonesia tahun 2020 (SP2020) untuk pertama kalinya menggunakan metode kombinasi data...',
                'opsi_jawaban' => ['A' => 'Wawancara tatap muka saja', 'B' => 'Registrasi penduduk dan sensus lapangan', 'C' => 'Survei telepon saja', 'D' => 'Estimasi tanpa data lapangan'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'SP2020 menggunakan metode kombinasi, memanfaatkan data registrasi penduduk (Dukcapil) yang dilengkapi sensus lapangan.',
            ],
            [
                'pertanyaan' => 'Publikasi tahunan BPS yang memuat rangkuman data statistik seluruh Indonesia berjudul...',
                'opsi_jawaban' => ['A' => 'Statistik Indonesia', 'B' => 'Berita Resmi Statistik', 'C' => 'Buku Saku BPS', 'D' => 'Indikator Ekonomi'],
                'kunci_jawaban' => 'A',
                'pembahasan' => '"Statistik Indonesia" adalah publikasi tahunan BPS yang merangkum data statistik dari berbagai bidang di seluruh Indonesia.',
            ],
            [
                'pertanyaan' => 'Apa fungsi utama Berita Resmi Statistik (BRS) yang rutin dirilis BPS?',
                'opsi_jawaban' => [
                    'A' => 'Mengumumkan hasil rilis data terbaru secara resmi dan berkala kepada publik',
                    'B' => 'Mengumumkan kebijakan anggaran pemerintah',
                    'C' => 'Menyampaikan hasil pemilu',
                    'D' => 'Mengatur kurikulum pendidikan statistik',
                ],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'BRS adalah rilis resmi dan berkala (misalnya inflasi bulanan atau pertumbuhan ekonomi triwulanan) yang dipublikasikan BPS ke masyarakat.',
            ],
            [
                'pertanyaan' => 'Semboyan/motto BPS adalah "Penyedia Data Statistik Terpercaya untuk ..."',
                'opsi_jawaban' => ['A' => 'Pemerintah', 'B' => 'Semua', 'C' => 'Peneliti', 'D' => 'Dunia Usaha'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Motto BPS adalah "Penyedia Data Statistik Terpercaya untuk Semua", menegaskan data BPS terbuka dan dapat diandalkan oleh siapa saja.',
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
            [
                'pertanyaan' => 'Indikator yang mengukur tingkat ketimpangan pengeluaran/pendapatan penduduk disebut...',
                'opsi_jawaban' => ['A' => 'Gini Ratio', 'B' => 'Indeks Harga Konsumen', 'C' => 'Nilai Tukar Petani', 'D' => 'Rasio Ketergantungan'],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'Gini Ratio (Rasio Gini) mengukur tingkat ketimpangan distribusi pendapatan/pengeluaran, dengan nilai 0 (merata sempurna) hingga 1 (timpang sempurna).',
            ],
            [
                'pertanyaan' => 'Penduduk digolongkan miskin oleh BPS apabila rata-rata pengeluaran per kapita per bulan berada di bawah...',
                'opsi_jawaban' => ['A' => 'Upah Minimum Regional', 'B' => 'Garis Kemiskinan', 'C' => 'Indeks Harga Konsumen', 'D' => 'PDRB per kapita'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Garis Kemiskinan adalah nilai rupiah pengeluaran minimum untuk memenuhi kebutuhan dasar; penduduk di bawahnya digolongkan miskin.',
            ],
            [
                'pertanyaan' => 'Nilai Tukar Petani (NTP) digunakan untuk mengukur...',
                'opsi_jawaban' => [
                    'A' => 'Tingkat kesejahteraan petani dari selisih harga jual hasil produksi dan harga barang yang dikonsumsi/biaya produksi',
                    'B' => 'Jumlah petani di suatu wilayah',
                    'C' => 'Luas lahan pertanian nasional',
                    'D' => 'Harga pupuk bersubsidi',
                ],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'NTP membandingkan indeks harga yang diterima petani dengan indeks harga yang dibayar petani, sebagai proksi kesejahteraan petani.',
            ],
            [
                'pertanyaan' => 'Perbandingan antara penduduk usia tidak produktif dengan penduduk usia produktif disebut...',
                'opsi_jawaban' => ['A' => 'Rasio Jenis Kelamin', 'B' => 'Rasio Ketergantungan', 'C' => 'Angka Harapan Hidup', 'D' => 'Laju Pertumbuhan Penduduk'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Rasio Ketergantungan (Dependency Ratio) membandingkan penduduk usia non-produktif (di bawah 15 dan di atas 64 tahun) terhadap penduduk usia produktif.',
            ],
            [
                'pertanyaan' => 'Selisih antara nilai ekspor dan nilai impor suatu negara disebut...',
                'opsi_jawaban' => ['A' => 'Neraca perdagangan', 'B' => 'Produk Domestik Bruto', 'C' => 'Indeks Harga Konsumen', 'D' => 'Nilai Tukar Petani'],
                'kunci_jawaban' => 'A',
                'pembahasan' => 'Neraca perdagangan adalah selisih nilai ekspor dikurangi nilai impor; positif berarti surplus, negatif berarti defisit.',
            ],
            [
                'pertanyaan' => 'Laju Pertumbuhan Ekonomi suatu wilayah umumnya dihitung dari perubahan...',
                'opsi_jawaban' => ['A' => 'Jumlah penduduk', 'B' => 'PDRB atas dasar harga konstan', 'C' => 'Indeks Pembangunan Manusia', 'D' => 'Jumlah rumah tangga miskin'],
                'kunci_jawaban' => 'B',
                'pembahasan' => 'Laju pertumbuhan ekonomi dihitung dari persentase perubahan PDRB atas dasar harga konstan antar periode, agar tidak terpengaruh inflasi.',
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
