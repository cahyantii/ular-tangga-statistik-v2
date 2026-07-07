<?php

namespace Database\Seeders;

use App\Models\KategoriMateri;
use App\Models\Materi;
use Illuminate\Database\Seeder;

class MateriSeeder extends Seeder
{
    public function run(): void
    {
        $statistikaDasar = KategoriMateri::where('slug', 'statistika-dasar')->firstOrFail();
        $pengenalanBps = KategoriMateri::where('slug', 'pengenalan-bps')->firstOrFail();
        $indikatorStatistik = KategoriMateri::where('slug', 'indikator-statistik')->firstOrFail();

        $materi = [
            // Statistika Dasar
            [
                'kategori_id' => $statistikaDasar->id,
                'urutan' => 1,
                'judul' => 'Pengertian Statistika',
                'konten' => "Statistika adalah ilmu yang mempelajari cara mengumpulkan, mengolah, menganalisis, menyajikan, dan menginterpretasikan data untuk membantu pengambilan keputusan. Statistika berbeda dengan statistik: statistik adalah kumpulan data atau angka itu sendiri (misalnya statistik penduduk), sedangkan statistika adalah ilmu yang mempelajari cara mengolah data tersebut.\n\nSecara umum, statistika dibagi menjadi dua cabang besar. Statistika deskriptif berfokus pada peringkasan dan penyajian data agar mudah dipahami, misalnya melalui tabel, grafik, atau ukuran seperti rata-rata. Statistika inferensia melangkah lebih jauh dengan menarik kesimpulan atau membuat prediksi tentang suatu populasi berdasarkan data sampel yang diambil.\n\nDalam kehidupan sehari-hari, statistika dipakai di hampir semua bidang: pemerintah menggunakannya untuk merencanakan pembangunan, perusahaan menggunakannya untuk riset pasar, dan peneliti menggunakannya untuk menguji hipotesis ilmiah. Karena itu, memahami statistika dasar membantu kita membaca berita, laporan, dan data di sekitar kita secara lebih kritis.",
            ],
            [
                'kategori_id' => $statistikaDasar->id,
                'urutan' => 2,
                'judul' => 'Mean, Median, dan Modus',
                'konten' => "Mean, median, dan modus adalah tiga ukuran pemusatan data yang paling sering digunakan untuk meringkas sekumpulan data menjadi satu nilai yang mewakili.\n\nMean (rata-rata hitung) diperoleh dengan menjumlahkan seluruh nilai data lalu membaginya dengan banyaknya data. Misalnya, nilai ujian 5 siswa adalah 70, 80, 90, 60, 100, maka mean-nya adalah (70+80+90+60+100)/5 = 80.\n\nMedian adalah nilai tengah setelah data diurutkan dari terkecil ke terbesar. Jika banyaknya data ganjil, median adalah nilai tepat di tengah. Jika genap, median adalah rata-rata dua nilai tengah. Median sangat berguna ketika data memiliki nilai ekstrem (outlier) yang bisa membuat mean menjadi menyesatkan.\n\nModus adalah nilai yang paling sering muncul dalam sekumpulan data. Suatu data bisa tidak memiliki modus, memiliki satu modus, atau lebih dari satu modus (multimodal). Modus sangat berguna untuk data kategorik, misalnya warna favorit atau jenis kelamin.",
            ],
            [
                'kategori_id' => $statistikaDasar->id,
                'urutan' => 3,
                'judul' => 'Membaca Tabel dan Grafik',
                'konten' => "Tabel dan grafik adalah alat utama untuk menyajikan data statistik agar lebih mudah dipahami dibanding hanya membaca angka mentah. Tabel biasanya digunakan ketika kita perlu menampilkan data secara rinci dan tepat, misalnya jumlah penduduk per provinsi.\n\nGrafik batang (bar chart) cocok untuk membandingkan nilai antar kategori, misalnya jumlah penduduk antar provinsi. Grafik garis (line chart) cocok untuk menunjukkan tren atau perubahan suatu nilai dari waktu ke waktu, misalnya tingkat inflasi bulanan. Diagram lingkaran (pie chart) cocok untuk menunjukkan proporsi atau persentase bagian terhadap keseluruhan, misalnya komposisi lapangan pekerjaan penduduk.\n\nSaat membaca grafik, penting untuk memperhatikan judul, satuan, skala sumbu, dan sumber data agar tidak salah menafsirkan informasi yang disajikan.",
            ],

            // Pengenalan BPS
            [
                'kategori_id' => $pengenalanBps->id,
                'urutan' => 1,
                'judul' => 'Apa itu BPS',
                'konten' => "Badan Pusat Statistik (BPS) adalah Lembaga Pemerintah Non-Kementerian di Indonesia yang bertugas menyelenggarakan kegiatan statistik secara nasional. Cikal bakal BPS dimulai sejak tahun 1960 melalui Undang-Undang Nomor 6 dan Nomor 7 Tahun 1960 tentang Sensus dan Statistik, yang kemudian terus berkembang hingga menjadi BPS seperti sekarang melalui Undang-Undang Nomor 16 Tahun 1997 tentang Statistik.\n\nSetiap tanggal 26 September diperingati sebagai Hari Statistik Nasional untuk mengenang lahirnya lembaga statistik resmi di Indonesia.\n\nBPS bertugas menghasilkan data statistik yang berkualitas, akurat, dan terpercaya sebagai dasar perencanaan pembangunan nasional maupun daerah. Data BPS digunakan oleh pemerintah, akademisi, pelaku usaha, hingga masyarakat umum untuk berbagai keperluan, mulai dari perumusan kebijakan hingga penelitian ilmiah.",
            ],
            [
                'kategori_id' => $pengenalanBps->id,
                'urutan' => 2,
                'judul' => 'Tugas dan Fungsi BPS',
                'konten' => "BPS memiliki tiga fungsi utama sebagai penyedia data statistik nasional. Pertama, BPS menyelenggarakan sensus dan survei berskala nasional yang datanya dipakai lintas kementerian dan lembaga. Kedua, BPS membina dan mengoordinasikan kegiatan statistik yang dilakukan oleh kementerian, lembaga, dan pemerintah daerah agar data yang dihasilkan seragam dan tidak tumpang tindih. Ketiga, BPS mengembangkan serta mempromosikan standar statistik nasional.\n\nBeberapa produk data yang rutin dirilis BPS antara lain angka kemiskinan, tingkat pengangguran, inflasi, pertumbuhan ekonomi, dan Indeks Pembangunan Manusia (IPM). Data-data ini menjadi acuan utama pemerintah dalam merancang kebijakan publik, seperti penentuan upah minimum, alokasi bantuan sosial, dan target pembangunan daerah.",
            ],
            [
                'kategori_id' => $pengenalanBps->id,
                'urutan' => 3,
                'judul' => 'Sensus dan Survei BPS',
                'konten' => "BPS menyelenggarakan tiga sensus besar secara berkala. Sensus Penduduk dilaksanakan setiap 10 tahun pada tahun yang berakhiran angka 0 (misalnya 2020, 2030) untuk mendata seluruh penduduk Indonesia. Sensus Pertanian dilaksanakan setiap 10 tahun pada tahun berakhiran angka 3 (misalnya 2013, 2023) untuk mendata seluruh usaha pertanian. Sensus Ekonomi dilaksanakan setiap 10 tahun pada tahun berakhiran angka 6 (misalnya 2016, 2026) untuk mendata seluruh usaha di luar sektor pertanian.\n\nSelain sensus, BPS juga rutin menyelenggarakan berbagai survei antarwaktu yang lebih sering, seperti Survei Sosial Ekonomi Nasional (Susenas) untuk mengukur kesejahteraan rumah tangga, dan Survei Angkatan Kerja Nasional (Sakernas) untuk mengukur kondisi ketenagakerjaan. Berbeda dengan sensus yang mendata seluruh populasi, survei umumnya menggunakan sampel yang mewakili populasi agar lebih efisien dari segi biaya dan waktu.",
            ],

            // Indikator Statistik
            [
                'kategori_id' => $indikatorStatistik->id,
                'urutan' => 1,
                'judul' => 'Indeks Pembangunan Manusia (IPM)',
                'konten' => "Indeks Pembangunan Manusia (IPM) atau Human Development Index adalah indikator yang mengukur pencapaian pembangunan manusia suatu wilayah berdasarkan tiga dimensi dasar: umur panjang dan hidup sehat, pengetahuan, dan standar hidup layak.\n\nDimensi umur panjang dan sehat diukur melalui angka harapan hidup saat lahir. Dimensi pengetahuan diukur melalui kombinasi harapan lama sekolah dan rata-rata lama sekolah penduduk usia dewasa. Dimensi standar hidup layak diukur melalui pengeluaran per kapita yang disesuaikan.\n\nNilai IPM berkisar antara 0 hingga 100, semakin tinggi nilainya semakin baik tingkat pembangunan manusia di wilayah tersebut. IPM sering digunakan untuk membandingkan tingkat kesejahteraan antarwilayah maupun antarnegara, serta menjadi salah satu indikator utama keberhasilan pembangunan suatu daerah.",
            ],
            [
                'kategori_id' => $indikatorStatistik->id,
                'urutan' => 2,
                'judul' => 'PDRB dan Pertumbuhan Ekonomi',
                'konten' => "Produk Domestik Regional Bruto (PDRB) adalah nilai total barang dan jasa yang dihasilkan oleh seluruh unit ekonomi di suatu wilayah dalam periode tertentu, biasanya satu tahun. PDRB adalah versi regional dari Produk Domestik Bruto (PDB) yang mengukur perekonomian nasional secara keseluruhan.\n\nPDRB dapat dihitung berdasarkan harga berlaku (PDRB nominal, mencerminkan nilai pada tahun berjalan) maupun harga konstan (PDRB riil, telah disesuaikan terhadap inflasi sehingga bisa dipakai membandingkan pertumbuhan riil antartahun).\n\nPertumbuhan ekonomi suatu wilayah dihitung dari persentase perubahan PDRB riil dari tahun ke tahun. Angka ini menjadi salah satu indikator paling penting untuk menilai kinerja perekonomian suatu daerah atau negara.",
            ],
            [
                'kategori_id' => $indikatorStatistik->id,
                'urutan' => 3,
                'judul' => 'Kemiskinan, Pengangguran, dan Inflasi',
                'konten' => "Tingkat kemiskinan diukur BPS berdasarkan konsep kemampuan memenuhi kebutuhan dasar. Penduduk dikategorikan miskin apabila memiliki rata-rata pengeluaran per kapita per bulan di bawah garis kemiskinan, yaitu nilai rupiah minimum yang dibutuhkan untuk memenuhi kebutuhan dasar makanan dan bukan makanan.\n\nTingkat Pengangguran Terbuka (TPT) adalah persentase jumlah pengangguran terhadap total angkatan kerja. Angkatan kerja sendiri terdiri dari penduduk usia kerja yang bekerja maupun yang sedang mencari pekerjaan. TPT menjadi indikator penting untuk melihat kesehatan pasar tenaga kerja suatu wilayah.\n\nInflasi adalah kecenderungan kenaikan harga barang dan jasa secara umum dan terus-menerus dalam periode tertentu, yang menyebabkan turunnya daya beli uang. BPS mengukur inflasi melalui Indeks Harga Konsumen (IHK) yang memantau perubahan harga sekumpulan barang dan jasa yang biasa dikonsumsi rumah tangga. Inflasi yang terlalu tinggi maupun deflasi (penurunan harga secara umum) sama-sama dapat mengganggu stabilitas ekonomi.",
            ],
        ];

        foreach ($materi as $item) {
            Materi::updateOrCreate(
                ['kategori_id' => $item['kategori_id'], 'judul' => $item['judul']],
                [
                    'konten' => $item['konten'],
                    'urutan' => $item['urutan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
