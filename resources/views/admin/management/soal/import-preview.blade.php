<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Pratinjau Import Soal</h1>

    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-sm font-medium text-emerald-700">Baris Valid</p>
            <p class="mt-1 text-2xl font-bold text-emerald-900">{{ count($valid) }}</p>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <p class="text-sm font-medium text-red-700">Baris Bermasalah</p>
            <p class="mt-1 text-2xl font-bold text-red-900">{{ count($invalid) }}</p>
        </div>
    </div>

    @if (count($invalid) > 0)
        <div class="mb-6 overflow-hidden rounded-2xl border border-red-200 bg-white">
            <div class="border-b border-red-100 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700">
                Baris yang tidak akan diimpor
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2">Baris</th>
                        <th class="px-4 py-2">Pertanyaan</th>
                        <th class="px-4 py-2">Kesalahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($invalid as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-500">#{{ $item['row_number'] }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit($item['raw']['pertanyaan'] ?? '(kosong)', 60) }}</td>
                            <td class="px-4 py-2 text-red-600">{{ implode(' ', $item['errors']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if (count($valid) > 0)
        <div class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
            <div class="border-b border-slate-100 bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-700">
                Baris yang akan diimpor
            </div>
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-2">Baris</th>
                        <th class="px-4 py-2">Kategori</th>
                        <th class="px-4 py-2">Pertanyaan</th>
                        <th class="px-4 py-2">Kunci</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($valid as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-500">#{{ $item['row_number'] }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item['kategori_nama'] }}</td>
                            <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit($item['pertanyaan'], 60) }}</td>
                            <td class="px-4 py-2 text-slate-600">{{ $item['kunci_jawaban'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="flex justify-end gap-3">
        <a href="{{ route('admin.management.soal.import.create') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</a>

        @if (count($valid) > 0)
            <form method="POST" action="{{ route('admin.management.soal.import.confirm') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="extension" value="{{ $extension }}">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Konfirmasi Import {{ count($valid) }} Soal
                </button>
            </form>
        @endif
    </div>
</x-admin-layout>
