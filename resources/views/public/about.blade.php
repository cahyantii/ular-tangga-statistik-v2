<x-public-layout title="Tentang - {{ config('app.name') }}">
    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h1 class="text-4xl font-extrabold text-slate-900 text-center">Tentang Ular Tangga Statistik Indonesia</h1>
        <p class="mt-6 text-lg text-slate-600 leading-relaxed">
            Ular Tangga Statistik Indonesia adalah game edukasi berbasis web yang menggabungkan permainan
            tradisional ular tangga dengan pembelajaran statistika dan pengenalan Badan Pusat Statistik (BPS).
            Kami percaya bahwa literasi statistik adalah keterampilan penting yang sebaiknya dikuasai sejak dini,
            dan cara terbaik untuk belajar adalah melalui pengalaman yang menyenangkan.
        </p>

        <div class="mt-12 grid gap-8 sm:grid-cols-2">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Tujuan Kami</h2>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Meningkatkan literasi statistik masyarakat Indonesia</li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Memperkenalkan tugas dan fungsi Badan Pusat Statistik</li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Membuat pembelajaran statistika terasa menyenangkan</li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Memberikan pengalaman belajar melalui gamifikasi</li>
                </ul>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Untuk Siapa?</h2>
                <ul class="mt-4 space-y-3 text-slate-600">
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Pelajar SD, SMP, dan SMA</li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Mahasiswa</li>
                    <li class="flex gap-3"><span class="text-emerald-600 font-bold">&#10003;</span> Masyarakat umum yang ingin belajar statistik</li>
                </ul>
            </div>
        </div>

        <div class="mt-12 rounded-2xl bg-emerald-50 p-8">
            <h2 class="text-xl font-semibold text-slate-900">Mengapa Badan Pusat Statistik?</h2>
            <p class="mt-3 text-slate-600 leading-relaxed">
                BPS adalah lembaga yang menghasilkan data resmi negara - mulai dari angka kemiskinan, inflasi,
                hingga Indeks Pembangunan Manusia. Data-data ini memengaruhi kebijakan yang berdampak langsung
                pada kehidupan kita. Dengan memahami dasar-dasar statistik dan peran BPS, kita menjadi warga
                negara yang lebih kritis dalam membaca informasi dan data di sekitar kita.
            </p>
        </div>
    </section>
</x-public-layout>
