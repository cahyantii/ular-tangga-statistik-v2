@php
    $canImport = empty($papan_errors) && empty($petak_errors);
@endphp

<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Pratinjau Import Papan</h1>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="rounded-2xl border p-4 {{ $canImport ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
            <p class="text-sm font-medium {{ $canImport ? 'text-emerald-700' : 'text-red-700' }}">Papan</p>
            <p class="mt-1 text-lg font-bold {{ $canImport ? 'text-emerald-900' : 'text-red-900' }}">{{ $canImport ? 'Valid' : 'Ada Kesalahan' }}</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-4">
            <p class="text-sm font-medium text-slate-600">Total Petak</p>
            <p class="mt-1 text-2xl font-bold text-slate-900">{{ count($petak) }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">Konektor Valid</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900">{{ count($konektor_valid) }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-medium text-red-700">Konektor Bermasalah</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ count($konektor_invalid) }}</p>
        </div>
    </div>

    @if (! empty($papan_errors))
        <div class="mb-6 rounded-2xl border border-red-200 bg-white p-4">
            <p class="mb-2 text-sm font-semibold text-red-700">Kesalahan data papan</p>
            <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                @foreach ($papan_errors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! empty($petak_errors))
        <div class="mb-6 rounded-2xl border border-red-200 bg-white p-4">
            <p class="mb-2 text-sm font-semibold text-red-700">Kesalahan data petak (import dibatalkan seluruhnya)</p>
            <ul class="list-inside list-disc space-y-1 text-sm text-red-600">
                @foreach ($petak_errors as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (count($konektor_invalid) > 0)
        <div class="mb-6 overflow-hidden rounded-2xl border border-red-200 bg-white">
            <div class="border-b border-red-100 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700">
                Konektor yang akan dilewati (papan tetap diimpor tanpa konektor ini)
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2">Baris</th>
                        <th class="px-4 py-2">Posisi Awal &rarr; Akhir</th>
                        <th class="px-4 py-2">Kesalahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($konektor_invalid as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-500">#{{ $item['row_number'] }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ $item['raw']['posisi_awal'] ?? '?' }} &rarr; {{ $item['raw']['posisi_akhir'] ?? '?' }}</td>
                            <td class="px-4 py-2 text-red-600">{{ implode(' ', $item['errors']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($konektor_valid) > 0)
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                Konektor yang akan diimpor
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2">Jenis</th>
                        <th class="px-4 py-2">Posisi Awal &rarr; Akhir</th>
                        <th class="px-4 py-2">Label</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($konektor_valid as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-700">{{ $item['jenis'] }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item['posisi_awal'] }} &rarr; {{ $item['posisi_akhir'] }}</td>
                            <td class="px-4 py-2 text-slate-500">{{ $item['label'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.management.papan-permainan.import.create') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>

        @if ($canImport)
            <form method="POST" action="{{ route('admin.management.papan-permainan.import.confirm') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="extension" value="{{ $extension }}">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Konfirmasi Import Papan "{{ $papan['nama'] ?? '' }}"
                </button>
            </form>
        @endif
    </div>
</x-admin-layout>
