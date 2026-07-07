<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verifikasi Sertifikat &mdash; {{ config('app.name') }}</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-slate-50 font-sans antialiased">
        <div class="mx-auto flex min-h-screen max-w-lg flex-col justify-center px-4 py-12">
            <div class="mb-6 text-center">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-extrabold text-emerald-700">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white">UT</span>
                    Ular Tangga Statistik
                </a>
            </div>

            @if ($certificate)
                <div class="rounded-2xl border-2 border-emerald-500 bg-emerald-50 p-6 text-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Sertifikat Terverifikasi</p>
                    <p class="mt-3 text-xl font-bold text-emerald-900">{{ $certificate->judul }}</p>
                    <p class="mt-1 text-slate-700">diberikan kepada</p>
                    <p class="mt-1 text-lg font-semibold text-slate-900">{{ $certificate->user->name }}</p>

                    <div class="mt-6 grid grid-cols-2 gap-4 text-left text-sm">
                        <div>
                            <p class="text-slate-500">Nomor Sertifikat</p>
                            <p class="font-medium text-slate-800">{{ $certificate->nomor_sertifikat }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Terbit</p>
                            <p class="font-medium text-slate-800">{{ $certificate->issued_at->translatedFormat('d F Y') }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-2xl border-2 border-red-300 bg-red-50 p-6 text-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-red-700">Tidak Ditemukan</p>
                    <p class="mt-3 text-slate-700">Kode verifikasi tidak cocok dengan sertifikat mana pun yang pernah diterbitkan.</p>
                </div>
            @endif
        </div>
    </body>
</html>
