<x-public-layout :title="config('app.name')">
    <!-- Hero -->
    <section class="relative overflow-hidden bg-gradient-to-b from-emerald-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 text-center">
            <span class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-1.5 text-sm font-medium text-emerald-800 mb-6">
                Belajar Statistik Jadi Seru
            </span>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-slate-900">
                Ular Tangga <span class="text-emerald-600">Statistik Indonesia</span>
            </h1>
            <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-600">
                Mainkan ular tangga digital sambil belajar Statistika, mengenal Badan Pusat Statistik (BPS),
                dan memahami indikator penting bangsa - IPM, PDRB, kemiskinan, pengangguran, hingga inflasi.
            </p>
            <div class="mt-10 flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="inline-flex items-center rounded-xl bg-emerald-600 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition">
                    Mulai Bermain Gratis
                </a>
                <a href="{{ route('how-to-play') }}" class="inline-flex items-center rounded-xl border border-slate-300 px-6 py-3 text-base font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Lihat Cara Bermain
                </a>
            </div>
        </div>
    </section>

    <!-- Mode Permainan -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-slate-900">Dua Cara Seru untuk Bermain</h2>
            <p class="mt-3 text-slate-600">Pilih tantangan sesuai suasana hatimu.</p>
        </div>
        <div class="grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 p-8 hover:shadow-lg transition">
                <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-xl mb-4">R</div>
                <h3 class="text-xl font-semibold text-slate-900">Bermain Melawan Robot</h3>
                <p class="mt-2 text-slate-600">Latih kemampuanmu kapan saja melawan AI yang siap menantang tanpa perlu menunggu lawan.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 p-8 hover:shadow-lg transition">
                <div class="h-12 w-12 rounded-xl bg-purple-100 flex items-center justify-center text-purple-700 font-bold text-xl mb-4">M</div>
                <h3 class="text-xl font-semibold text-slate-900">Multiplayer Real-Time</h3>
                <p class="mt-2 text-slate-600">Tantang temanmu secara langsung dalam satu room, lempar dadu dan jawab soal bersamaan secara real-time.</p>
            </div>
        </div>
    </section>

    <!-- Materi -->
    <section class="bg-slate-50 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-slate-900">Apa yang Akan Kamu Pelajari?</h2>
                <p class="mt-3 text-slate-600">Materi disusun agar mudah dipahami pelajar SD hingga masyarakat umum.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-3">
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Statistika Dasar</h3>
                    <p class="mt-2 text-sm text-slate-600">Mean, median, modus, serta cara membaca tabel dan grafik.</p>
                </div>
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Pengenalan BPS</h3>
                    <p class="mt-2 text-sm text-slate-600">Tugas, fungsi, serta sensus dan survei yang diselenggarakan BPS.</p>
                </div>
                <div class="rounded-2xl bg-white p-8 shadow-sm">
                    <h3 class="text-lg font-semibold text-slate-900">Indikator Statistik</h3>
                    <p class="mt-2 text-sm text-slate-600">IPM, PDRB, kemiskinan, pengangguran, dan inflasi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <h2 class="text-3xl font-bold text-slate-900">Siap Memulai Petualangan Statistikmu?</h2>
        <p class="mt-3 text-slate-600">Gratis untuk semua kalangan - pelajar, mahasiswa, hingga masyarakat umum.</p>
        <a href="{{ route('register') }}" class="mt-8 inline-flex items-center rounded-xl bg-emerald-600 px-8 py-3 text-base font-semibold text-white shadow-lg shadow-emerald-600/20 hover:bg-emerald-700 transition">
            Daftar Sekarang
        </a>
    </section>
</x-public-layout>
