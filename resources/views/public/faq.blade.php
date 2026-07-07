<x-public-layout title="FAQ - {{ config('app.name') }}">
    <section class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-4xl font-extrabold text-slate-900 text-center">Pertanyaan yang Sering Diajukan</h1>

        <div class="mt-12 space-y-4">
            @foreach ([
                [
                    'q' => 'Apakah Ular Tangga Statistik Indonesia gratis?',
                    'a' => 'Ya, seluruh fitur dapat digunakan secara gratis untuk pelajar, mahasiswa, dan masyarakat umum.',
                ],
                [
                    'q' => 'Apa perbedaan mode Vs Robot dan Multiplayer?',
                    'a' => 'Vs Robot memungkinkan kamu bermain sendirian melawan AI kapan saja. Multiplayer mempertemukan kamu dengan pemain lain secara real-time melalui Quick Match (pencarian otomatis) atau Private Room (kode room).',
                ],
                [
                    'q' => 'Bagaimana sistem skor bekerja?',
                    'a' => 'Jawaban benar mendapat +10 poin, jawaban salah -5 poin, petak bonus +20 poin, dan memenangkan permainan +100 poin.',
                ],
                [
                    'q' => 'Apa yang terjadi jika koneksi saya terputus saat multiplayer?',
                    'a' => 'Permainan akan dijeda (Paused) dan kamu diberi waktu 60 detik untuk kembali. Jika tidak kembali dalam waktu tersebut, kamu dinyatakan kalah (forfeit) dan lawan dinyatakan menang.',
                ],
                [
                    'q' => 'Bagaimana cara mendapatkan sertifikat digital?',
                    'a' => 'Sertifikat diberikan setelah kamu menyelesaikan seluruh kategori materi, mencapai akurasi jawaban minimal 80% secara keseluruhan, dan menyelesaikan minimal satu permainan hingga selesai.',
                ],
                [
                    'q' => 'Apakah soal bisa muncul berulang dalam satu permainan?',
                    'a' => 'Tidak. Dalam satu sesi permainan, soal yang sama tidak akan muncul dua kali. Daftar soal yang sudah dipakai akan direset pada permainan berikutnya.',
                ],
            ] as $faq)
                <div x-data="{ open: false }" class="rounded-xl border border-slate-200 overflow-hidden">
                    <button
                        type="button"
                        @click="open = !open"
                        class="w-full flex items-center justify-between px-6 py-4 text-left font-semibold text-slate-900 hover:bg-slate-50"
                    >
                        {{ $faq['q'] }}
                        <span x-text="open ? '−' : '+'" class="text-emerald-600 text-xl font-bold"></span>
                    </button>
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 -translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="px-6 pb-4 text-slate-600"
                    >
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-public-layout>
