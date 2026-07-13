<x-public-layout title="Syarat & Ketentuan - {{ config('app.name') }}">
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl">Syarat &amp; Ketentuan</h1>
        <p class="mt-3 text-sm text-slate-500">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

        <div class="mt-8 space-y-6 leading-relaxed text-slate-600">
            <p>
                Dengan membuat akun dan menggunakan Ular Tangga Statistik Indonesia, kamu setuju untuk menggunakan
                platform ini secara bertanggung jawab, sesuai tujuannya sebagai media belajar statistik yang
                interaktif untuk pelajar, mahasiswa, dan masyarakat umum.
            </p>
            <p>
                Halaman ini adalah kerangka awal Syarat &amp; Ketentuan dan akan dilengkapi lebih lanjut oleh
                pengelola platform. Jika kamu punya pertanyaan terkait ketentuan penggunaan akun, silakan hubungi
                kami melalui halaman <a href="{{ route('faq') }}" class="font-semibold text-primary-600 underline">FAQ</a>.
            </p>
        </div>
    </section>
</x-public-layout>
