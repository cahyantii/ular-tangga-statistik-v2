@php
    $boardConfig = [
        'jumlah_kolom' => $papan->jumlah_kolom,
        'jumlah_petak' => $papan->jumlah_petak,
        'petak' => $papan->petak->map(fn ($p) => [
            'id' => $p->id,
            'posisi' => $p->posisi,
            'jenis_petak' => $p->jenis_petak->value,
            'is_active' => $p->is_active,
            'label' => $p->label,
        ])->values(),
        'konektor' => $papan->papanKonektor->map(fn ($k) => [
            'posisi_awal' => $k->posisi_awal,
            'posisi_akhir' => $k->posisi_akhir,
            'jenis' => $k->jenis->value,
        ])->values(),
    ];

    $konektorRoutes = $konektorList->mapWithKeys(fn ($k) => [
        $k->posisi_awal => [
            'edit_url' => route('admin.management.papan-permainan.konektor.edit', [$papan, $k]),
            'delete_url' => route('admin.management.papan-permainan.konektor.destroy', [$papan, $k]),
        ],
    ]);

    $legend = [
        'tangga' => ['label' => 'Tangga (naik)', 'color' => '#10b981'],
        'ular' => ['label' => 'Ular (turun)', 'color' => '#e11d48'],
    ];
@endphp

<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/konektor-editor.js'])
    @endpush

    <script>
    function konektorEditor() {
        return {
            jumlahKolom: 0,
            jumlahPetak: 0,
            existingKonektor: [],
            origin: null,
            destination: null,
            jenis: 'tangga',
            label: '',
            icon: '',
            message: null,
            popover: null,
            geometry: null,
            existingLayerEl: null,
            previewLayerEl: null,
            existingRenderers: [],
            previewRenderer: null,
            konektorRoutes: {},

            init() {
                const root = document.getElementById('board-editor-data');
                this.konektorRoutes = JSON.parse(root.dataset.konektorRoutes || '{}');

                window.addEventListener('konektor:grid-ready', (e) => {
                    this.jumlahKolom = e.detail.config.jumlah_kolom;
                    this.jumlahPetak = e.detail.config.jumlah_petak;
                    this.existingKonektor = e.detail.config.konektor;
                    this.setupOverlayLayers();
                    this.renderExistingConnectors();
                });
                window.addEventListener('konektor:tile-click', (e) => this.handleClick(e.detail.tile));
            },

            popoverRoutes() {
                return this.popover ? (this.konektorRoutes[this.popover.posisi] ?? null) : null;
            },

            /**
             * Dua layer overlay POLOS (bukan lagi satu <svg> besar) — ular/
             * tangga sekarang dibangun lewat SnakeRenderer/LadderRenderer asli
             * (window.BoardVisualsKit, lihat konektor-editor.js), persis sama
             * dengan yang dipakai papan gameplay sungguhan (gradient badan,
             * rantai sisik, rel kayu bertekstur, dst — bukan versi sederhana
             * lama yang cuma satu garis tipis + 2 titik mata). Setiap renderer
             * membangun <div class="board-object"> + <svg> LOKAL miliknya
             * sendiri dan menumpuknya sebagai child biasa di salah satu layer
             * ini, sama seperti BoardRenderer.js di papan gameplay.
             */
            setupOverlayLayers() {
                const grid = document.getElementById('board-grid');
                const totalRows = Math.ceil(this.jumlahPetak / this.jumlahKolom);
                const coord = new window.BoardVisualsKit.CoordinateHelper(this.jumlahKolom, totalRows);
                this.geometry = new window.BoardVisualsKit.BoardGeometry(coord);

                // existingLayerEl digambar SEKALI (konektor yang sudah
                // tersimpan, pudar/muted) dan tidak pernah ikut terhapus saat
                // admin mulai memilih asal/tujuan baru - hanya previewLayerEl
                // yang dibersihkan berulang kali lewat clearPreview(). Tanpa
                // pemisahan ini, admin membuat tangga/ular baru tanpa tahu
                // jalur mana yang sudah dipakai, sehingga jalurnya bisa saling
                // menyilang tanpa disadari (lihat kasus 5 pasang konektor yang
                // berpotongan di PapanPermainanSeeder sebelum diperbaiki).
                this.existingLayerEl = document.createElement('div');
                this.existingLayerEl.className = 'pointer-events-none absolute inset-0';
                this.existingLayerEl.style.zIndex = '5';

                this.previewLayerEl = document.createElement('div');
                this.previewLayerEl.className = 'pointer-events-none absolute inset-0';
                this.previewLayerEl.style.zIndex = '6';

                grid.appendChild(this.existingLayerEl);
                grid.appendChild(this.previewLayerEl);
            },

            /** Satu konektor tersimpan -> satu instance SnakeRenderer/LadderRenderer asli, opacity diturunkan (muted). */
            buildConnectorRenderer(layer, jenis, start, end, themeIndex) {
                const cfg = { start, end, themeIndex, cols: this.jumlahKolom, rows: Math.ceil(this.jumlahPetak / this.jumlahKolom) };
                return jenis === 'ular'
                    ? new window.BoardVisualsKit.SnakeRenderer(layer, this.geometry, cfg)
                    : new window.BoardVisualsKit.LadderRenderer(layer, this.geometry, cfg);
            },

            renderExistingConnectors() {
                if (!this.existingLayerEl) {
                    return;
                }
                this.existingRenderers.forEach((r) => r.destroy?.());
                this.existingRenderers = [];
                this.existingLayerEl.innerHTML = '';

                let snakeIndex = 0;
                let ladderIndex = 0;
                this.existingKonektor.forEach((k) => {
                    const themeIndex = k.jenis === 'ular' ? snakeIndex++ : ladderIndex++;
                    const renderer = this.buildConnectorRenderer(this.existingLayerEl, k.jenis, k.posisi_awal, k.posisi_akhir, themeIndex);
                    renderer.wrap.style.opacity = '0.55';
                    this.existingRenderers.push(renderer);
                });
            },

            handleClick(tile) {
                this.popover = null;

                if (tile.posisi === 1 || tile.posisi === this.jumlahPetak) {
                    this.flash('Petak Start/Finish tidak bisa jadi asal atau tujuan konektor.');
                    return;
                }

                if (['tangga', 'ular'].includes(tile.jenis_petak) && !this.origin) {
                    this.popover = tile;
                    return;
                }

                if (!this.origin) {
                    this.origin = tile;
                    this.destination = null;
                    this.message = null;
                    this.clearPreview();
                    this.applyHighlights();
                    return;
                }

                if (this.origin.posisi === tile.posisi) {
                    this.reset();
                    return;
                }

                this.destination = tile;
                this.jenis = tile.posisi > this.origin.posisi ? 'tangga' : 'ular';
                this.applyHighlights();
                this.drawPreview();
            },

            reset() {
                this.origin = null;
                this.destination = null;
                this.label = '';
                this.icon = '';
                this.message = null;
                this.clearPreview();
                this.applyHighlights();
            },

            flash(msg) {
                this.message = msg;
                setTimeout(() => { this.message = null; }, 3500);
            },

            applyHighlights() {
                document.querySelectorAll('.board-tile').forEach((el) => { el.style.outline = ''; });
                if (this.origin) {
                    const cell = document.querySelector('[data-posisi="' + this.origin.posisi + '"]');
                    if (cell) { cell.style.outline = '3px solid #0ea5e9'; }
                }
                if (this.destination) {
                    const cell = document.querySelector('[data-posisi="' + this.destination.posisi + '"]');
                    if (cell) { cell.style.outline = '3px solid #f59e0b'; }
                }
            },

            clearPreview() {
                if (this.previewRenderer) {
                    this.previewRenderer.destroy?.();
                    this.previewRenderer.wrap?.remove();
                    this.previewRenderer = null;
                }
            },

            /** Preview konektor yang sedang dipilih admin (belum disimpan) — SnakeRenderer/LadderRenderer asli, opacity penuh. */
            drawPreview() {
                this.clearPreview();
                if (!this.origin || !this.destination || !this.previewLayerEl) {
                    return;
                }
                this.previewRenderer = this.buildConnectorRenderer(
                    this.previewLayerEl, this.jenis, this.origin.posisi, this.destination.posisi, 0,
                );
            },

            validationErrors() {
                if (!this.origin || !this.destination) {
                    return [];
                }
                const awal = this.origin.posisi, akhir = this.destination.posisi;
                const errors = [];

                if (this.existingKonektor.some((k) => k.posisi_awal === awal)) {
                    errors.push('Sudah ada konektor lain yang dimulai dari posisi ini.');
                }
                if (this.existingKonektor.some((k) => k.posisi_awal === akhir)) {
                    errors.push('Posisi tujuan tidak boleh sama dengan posisi awal konektor lain (mencegah chaining).');
                }
                if (this.jenis === 'tangga' && akhir <= awal) {
                    errors.push('Tangga harus naik: posisi akhir harus lebih besar dari posisi awal.');
                }
                if (this.jenis === 'ular' && akhir >= awal) {
                    errors.push('Ular harus turun: posisi akhir harus lebih kecil dari posisi awal.');
                }

                return errors;
            },
        };
    }
    </script>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Konektor — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">Klik petak asal, lalu petak tujuan, untuk membuat tangga atau ular baru.</p>
        </div>
        <a href="{{ route('admin.management.papan-permainan.index') }}"
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
            <x-player.icon name="arrow-right" class="h-4 w-4 rotate-180" />
            Kembali ke daftar papan
        </a>
    </div>

    <div class="mb-4 flex flex-wrap gap-3 rounded-2xl border border-slate-100 bg-white p-4 text-xs shadow-[0_10px_40px_rgba(0,0,0,.06)]">
        @foreach ($legend as $meta)
            <span class="inline-flex items-center gap-1.5">
                <span class="h-3 w-3 rounded" style="background-color: {{ $meta['color'] }}"></span>
                {{ $meta['label'] }}
            </span>
        @endforeach
        <span class="inline-flex items-center gap-1.5 text-slate-400">
            <span class="h-3 w-3 rounded border-2 border-sky-500"></span>
            Asal terpilih
        </span>
        <span class="inline-flex items-center gap-1.5 text-slate-400">
            <span class="h-3 w-3 rounded border-2 border-amber-500"></span>
            Tujuan terpilih
        </span>
        <span class="inline-flex items-center gap-1.5 text-slate-400">
            <span class="h-3 w-3 rounded bg-slate-300"></span>
            Konektor yang sudah ada (pudar) — hindari jalur baru menyilang garis ini
        </span>
    </div>

    <div x-data="konektorEditor()" class="relative">
        <div id="board-grid" class="relative rounded-[26px] border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)]"></div>

        <div id="board-editor-data"
             data-board="{{ json_encode($boardConfig) }}"
             data-konektor-routes="{{ json_encode($konektorRoutes) }}"></div>

        {{-- Info / blocked toast --}}
        <div x-show="message" x-transition x-cloak
             class="fixed bottom-6 left-1/2 z-40 -translate-x-1/2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-lg">
            <span x-text="message"></span>
        </div>

        {{-- Popover for existing tangga/ular tile --}}
        <template x-if="popover">
            <div x-transition x-cloak class="fixed bottom-6 left-1/2 z-40 w-72 -translate-x-1/2 rounded-2xl border border-slate-100 bg-white p-4 shadow-2xl">
                <p class="text-sm font-semibold text-slate-800">
                    Konektor di petak #<span x-text="popover?.posisi"></span>
                </p>
                <p class="mt-0.5 text-xs text-slate-500">Jenis: <span x-text="popover?.jenis_petak"></span></p>
                <div class="mt-3 flex gap-2">
                    <template x-if="popoverRoutes()">
                        <div class="flex gap-2">
                            <a :href="popoverRoutes().edit_url" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">Edit</a>
                            <form method="POST" :action="popoverRoutes().delete_url" @submit="if (!confirm('Hapus konektor ini?')) $event.preventDefault()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </template>
                </div>
                <button type="button" @click="popover = null" class="mt-3 text-xs text-slate-400 hover:text-slate-600">Tutup</button>
            </div>
        </template>

        {{-- Selection form (once origin + destination chosen) --}}
        <template x-if="origin && destination">
            <div x-transition x-cloak class="mt-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-[0_10px_40px_rgba(0,0,0,.06)]">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-slate-800">
                        Konektor: Petak #<span x-text="origin?.posisi"></span> &rarr; Petak #<span x-text="destination?.posisi"></span>
                    </p>
                    <button type="button" @click="reset()" class="text-xs font-semibold text-slate-500 hover:text-slate-700">Batal pilih</button>
                </div>

                <template x-if="validationErrors().length > 0">
                    <ul class="mt-3 space-y-1 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600">
                        <template x-for="err in validationErrors()" :key="err">
                            <li x-text="err"></li>
                        </template>
                    </ul>
                </template>

                <form method="POST" action="{{ route('admin.management.papan-permainan.konektor.store', $papan) }}" class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-4 sm:items-end">
                    @csrf
                    <input type="hidden" name="posisi_awal" :value="origin?.posisi">
                    <input type="hidden" name="posisi_akhir" :value="destination?.posisi">

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Jenis</label>
                        <select name="jenis" x-model="jenis" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                            <option value="tangga">Tangga</option>
                            <option value="ular">Ular</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Label (opsional)</label>
                        <input type="text" name="label" x-model="label" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">Icon (opsional)</label>
                        <input type="text" name="icon" x-model="icon" class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                    </div>
                    <button type="submit" :disabled="validationErrors().length > 0"
                            class="admin-nav-active rounded-lg px-4 py-2.5 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40">
                        Simpan Konektor
                    </button>
                </form>
            </div>
        </template>
    </div>

    {{-- Existing table view (unchanged) --}}
    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-100 text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Posisi Awal</th>
                    <th class="px-4 py-3">Posisi Akhir</th>
                    <th class="px-4 py-3">Label</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($konektorList as $konektor)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $konektor->jenis->label() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->posisi_awal }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->posisi_akhir }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $konektor->label ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.management.papan-permainan.konektor.edit', [$papan, $konektor]) }}" class="text-slate-600 hover:text-slate-900">Edit</a>
                            <form method="POST" action="{{ route('admin.management.papan-permainan.konektor.destroy', [$papan, $konektor]) }}" class="inline"
                                  onsubmit="return confirm('Hapus konektor ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-red-600 hover:text-red-800">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada konektor pada papan ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
