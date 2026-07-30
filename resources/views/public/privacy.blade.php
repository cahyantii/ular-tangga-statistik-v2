<x-public-layout title="Kebijakan Privasi - {{ config('app.name') }}">
    <section class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-extrabold text-slate-900 sm:text-4xl dark:text-slate-100">Kebijakan Privasi</h1>
        <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}</p>

        <div class="mt-8 space-y-6 leading-relaxed text-slate-600 dark:text-slate-400">
            <p>
                Kami menghargai privasi setiap pengguna. Data yang kamu berikan saat membuat akun — nama dan
                email — hanya digunakan untuk keperluan autentikasi, personalisasi progres belajar, dan papan
                peringkat di dalam platform ini, dan tidak dibagikan ke pihak ketiga untuk kepentingan komersial.
            </p>
            <p>
                Halaman ini adalah kerangka awal Kebijakan Privasi dan akan dilengkapi lebih lanjut oleh pengelola
                platform. Jika kamu punya pertanyaan terkait data akunmu, silakan hubungi kami melalui halaman
                <a href="{{ route('faq') }}" class="font-semibold text-primary-600 underline dark:text-primary-400">FAQ</a>.
            </p>
        </div>
    </section>
</x-public-layout>
