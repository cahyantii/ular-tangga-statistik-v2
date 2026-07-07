<x-public-layout title="Cara Bermain - {{ config('app.name') }}">
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-4xl font-extrabold text-slate-900 text-center">Cara Bermain</h1>
        <p class="mt-4 text-center text-slate-600">Ikuti langkah-langkah berikut untuk mulai bermain.</p>

        <ol class="mt-12 space-y-8">
            @foreach ([
                ['judul' => 'Masuk atau Daftar Akun', 'deskripsi' => 'Buat akun gratis atau masuk jika sudah terdaftar.'],
                ['judul' => 'Pilih Mode Permainan', 'deskripsi' => 'Pilih bermain melawan Robot (AI) atau Multiplayer Real-Time melawan pemain lain.'],
                ['judul' => 'Masuk ke Room Permainan', 'deskripsi' => 'Untuk mode multiplayer, gunakan Quick Match untuk mencari lawan otomatis, atau buat/gabung Private Room dengan kode.'],
                ['judul' => 'Lempar Dadu Digital', 'deskripsi' => 'Saat giliranmu tiba, klik tombol dadu. Pion akan bergerak sesuai angka yang keluar.'],
                ['judul' => 'Jawab Soal di Petak Soal', 'deskripsi' => 'Jika pion berhenti di petak soal, jawab pertanyaan seputar statistika/BPS sebelum waktu habis.'],
                ['judul' => 'Kumpulkan Poin', 'deskripsi' => 'Jawaban benar +10 poin, jawaban salah -5 poin, petak bonus +20 poin, dan menang +100 poin.'],
                ['judul' => 'Waspadai Tangga, Ular, Penalti, dan Mystery', 'deskripsi' => 'Tangga membawamu naik lebih cepat, ular menjatuhkanmu, petak penalti mengurangi poin, dan petak mystery memberi efek kejutan.'],
                ['judul' => 'Capai Petak Finish', 'deskripsi' => 'Permainan selesai ketika salah satu pemain mencapai petak Finish dengan angka dadu yang pas.'],
                ['judul' => 'Lihat Hasil dan Progres Belajarmu', 'deskripsi' => 'Setelah permainan selesai, lihat skor akhir, akurasi jawaban, achievement baru, dan progres belajarmu.'],
            ] as $index => $langkah)
                <li class="flex gap-5">
                    <span class="flex-none h-10 w-10 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center">
                        {{ $index + 1 }}
                    </span>
                    <div>
                        <h3 class="font-semibold text-slate-900">{{ $langkah['judul'] }}</h3>
                        <p class="mt-1 text-slate-600">{{ $langkah['deskripsi'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>

        <div class="mt-12 text-center">
            <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition">
                Mulai Bermain Sekarang
            </a>
        </div>
    </section>
</x-public-layout>
