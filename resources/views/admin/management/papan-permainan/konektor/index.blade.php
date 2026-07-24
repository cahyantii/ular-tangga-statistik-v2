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
        const SVG_NS = 'http://www.w3.org/2000/svg';

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
            svgEl: null,
            existingLayerEl: null,
            previewLayerEl: null,
            konektorRoutes: {},

            init() {
                const root = document.getElementById('board-editor-data');
                this.konektorRoutes = JSON.parse(root.dataset.konektorRoutes || '{}');

                window.addEventListener('konektor:grid-ready', (e) => {
                    this.jumlahKolom = e.detail.config.jumlah_kolom;
                    this.jumlahPetak = e.detail.config.jumlah_petak;
                    this.existingKonektor = e.detail.config.konektor;
                    this.setupSvgOverlay();
                    this.renderExistingConnectors();
                });
                window.addEventListener('konektor:tile-click', (e) => this.handleClick(e.detail.tile));
            },

            popoverRoutes() {
                return this.popover ? (this.konektorRoutes[this.popover.posisi] ?? null) : null;
            },

            setupSvgOverlay() {
                const grid = document.getElementById('board-grid');
                const totalRows = Math.ceil(this.jumlahPetak / this.jumlahKolom);
                const svg = document.createElementNS(SVG_NS, 'svg');
                svg.setAttribute('viewBox', `0 0 ${this.jumlahKolom} ${totalRows}`);
                svg.setAttribute('preserveAspectRatio', 'none');
                svg.style.gridRow = '1 / -1';
                svg.style.gridColumn = '1 / -1';
                svg.style.width = '100%';
                svg.style.height = '100%';
                svg.style.pointerEvents = 'none';
                // WAJIB: tanpa `position`, SVG ini "static" sementara setiap
                // sel .board-tile "relative" - urutan cat CSS meletakkan SEMUA
                // elemen positioned SETELAH elemen static apa pun urutan DOM-nya,
                // jadi SVG ini (kalaupun ditambahkan terakhir di DOM) akan tetap
                // tertutup total oleh sel-sel papan. `position: relative` + z-index
                // membuatnya ikut lapisan "positioned" supaya benar-benar di atas.
                svg.style.position = 'relative';
                svg.style.zIndex = '5';
                grid.appendChild(svg);
                this.svgEl = svg;

                // Dua layer terpisah dalam satu SVG yang sama: existingLayerEl
                // digambar SEKALI (konektor yang sudah tersimpan, pudar/muted)
                // dan tidak pernah ikut terhapus saat admin mulai memilih
                // asal/tujuan baru - hanya previewLayerEl yang dibersihkan
                // berulang kali lewat clearSvg(). Tanpa pemisahan ini, admin
                // membuat tangga/ular baru tanpa tahu jalur mana yang sudah
                // dipakai, sehingga jalurnya bisa saling menyilang tanpa
                // disadari (lihat kasus 5 pasang konektor yang berpotongan di
                // PapanPermainanSeeder sebelum diperbaiki).
                this.existingLayerEl = document.createElementNS(SVG_NS, 'g');
                this.previewLayerEl = document.createElementNS(SVG_NS, 'g');
                svg.appendChild(this.existingLayerEl);
                svg.appendChild(this.previewLayerEl);
            },

            renderExistingConnectors() {
                if (!this.existingLayerEl) {
                    return;
                }
                this.existingLayerEl.innerHTML = '';

                this.existingKonektor.forEach((k) => {
                    const from = this.cellPosition(k.posisi_awal);
                    const to = this.cellPosition(k.posisi_akhir);
                    if (!from || !to) {
                        return;
                    }
                    if (k.jenis === 'ular') {
                        this.drawSnakeCurve(from, to, this.existingLayerEl, true);
                    } else {
                        this.drawLadderCurve(from, to, this.existingLayerEl, true);
                    }
                });
            },

            cellPosition(posisi) {
                const cell = document.querySelector('[data-posisi="' + posisi + '"]');
                if (!cell) {
                    return null;
                }
                return { row: parseInt(cell.style.gridRow, 10), col: parseInt(cell.style.gridColumn, 10) };
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
                    this.clearSvg();
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
                this.clearSvg();
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

            clearSvg() {
                if (this.previewLayerEl) {
                    this.previewLayerEl.innerHTML = '';
                }
            },

            drawPreview() {
                this.clearSvg();
                if (!this.origin || !this.destination || !this.previewLayerEl) {
                    return;
                }
                const from = this.cellPosition(this.origin.posisi);
                const to = this.cellPosition(this.destination.posisi);
                if (!from || !to) {
                    return;
                }
                if (this.jenis === 'ular') {
                    this.drawSnakeCurve(from, to, this.previewLayerEl, false);
                } else {
                    this.drawLadderCurve(from, to, this.previewLayerEl, false);
                }
            },

            // muted=true dipakai renderExistingConnectors() untuk konektor yang
            // SUDAH tersimpan (opacity diturunkan) supaya jelas beda dari
            // konektor baru yang sedang dipilih admin (opacity penuh).
            //
            // Badan digambar sebagai SATU poligon meruncing (moncong kecil ->
            // rahang lebar -> leher menyempit -> badan -> ekor meruncing ke
            // titik) - versi statis ringkas dari anatomi yang sama dipakai
            // SnakeRenderer.js di papan permainan sungguhan. Sebelumnya cuma
            // satu garis stroke tipis tanpa lebar sama sekali, makanya
            // terlihat seperti belut/selang, bukan ular (lihat keluhan admin).
            drawSnakeCurve(from, to, layer = this.previewLayerEl, muted = false) {
                const x1 = from.col - 0.5, y1 = from.row - 0.5;
                const x2 = to.col - 0.5, y2 = to.row - 0.5;
                const dx = x2 - x1, dy = y2 - y1;
                const len = Math.max(Math.sqrt(dx * dx + dy * dy), 0.001);
                const ux = dx / len, uy = dy / len;
                const px = -uy, py = ux;
                const wave = Math.min(len * 0.32, 1.1);
                const c1x = x1 + ux * len * 0.25 + px * wave, c1y = y1 + uy * len * 0.25 + py * wave;
                const c2x = x1 + ux * len * 0.75 - px * wave, c2y = y1 + uy * len * 0.75 - py * wave;

                const cubicPoint = (t) => {
                    const mt = 1 - t;
                    const a = mt * mt * mt, b = 3 * mt * mt * t, c = 3 * mt * t * t, d = t * t * t;
                    return { x: a * x1 + b * c1x + c * c2x + d * x2, y: a * y1 + b * c1y + c * c2y + d * y2 };
                };
                const cubicTangent = (t) => {
                    const mt = 1 - t;
                    const tx = 3 * mt * mt * (c1x - x1) + 6 * mt * t * (c2x - c1x) + 3 * t * t * (x2 - c2x);
                    const ty = 3 * mt * mt * (c1y - y1) + 6 * mt * t * (c2y - c1y) + 3 * t * t * (y2 - c2y);
                    const tl = Math.max(Math.hypot(tx, ty), 0.0001);
                    return { x: tx / tl, y: ty / tl };
                };

                // t=0 selalu di petak ASAL (kepala ular, posisi lebih tinggi)
                // dan t=1 di petak TUJUAN (ujung ekor, posisi pendaratan) -
                // konsisten dengan urutan asal->tujuan yang sudah dipakai
                // di seluruh fungsi ini.
                const HEAD_W = 0.36, NECK_W = 0.16, BODY_W = 0.22;
                const widthAt = (t) => {
                    if (t < 0.08) return HEAD_W * (0.4 + 0.6 * (t / 0.08));
                    if (t < 0.18) return HEAD_W - (HEAD_W - NECK_W) * ((t - 0.08) / 0.10);
                    if (t < 0.82) return NECK_W + (BODY_W - NECK_W) * Math.min(1, (t - 0.18) / 0.06);
                    return BODY_W * (1 - (t - 0.82) / 0.18);
                };

                const STEPS = 18;
                const left = [], right = [];
                for (let i = 0; i <= STEPS; i++) {
                    const t = i / STEPS;
                    const p = cubicPoint(t);
                    const tan = cubicTangent(t);
                    const n = { x: -tan.y, y: tan.x };
                    const w = Math.max(widthAt(t), 0.001) / 2;
                    left.push({ x: p.x + n.x * w, y: p.y + n.y * w });
                    right.push({ x: p.x - n.x * w, y: p.y - n.y * w });
                }
                const outlinePts = [...left, ...right.reverse()];
                const d = `M ${outlinePts.map((p) => `${p.x.toFixed(3)},${p.y.toFixed(3)}`).join(' L ')} Z`;
                const opacity = muted ? '0.4' : '1';

                const path = document.createElementNS(SVG_NS, 'path');
                path.setAttribute('d', d);
                path.setAttribute('fill', '#e11d48');
                path.setAttribute('stroke', '#9f1239');
                path.setAttribute('stroke-width', '0.02');
                path.setAttribute('stroke-linejoin', 'round');
                path.setAttribute('opacity', opacity);
                layer.appendChild(path);

                // Mata: dua titik gelap kecil dekat moncong, supaya langsung
                // dikenali sebagai wajah ular (bukan sekadar bentuk pipa).
                const headPt = cubicPoint(0.05);
                const headTan = cubicTangent(0.05);
                const headNorm = { x: -headTan.y, y: headTan.x };
                const eyeOffset = HEAD_W * 0.32;
                [1, -1].forEach((side) => {
                    const eye = document.createElementNS(SVG_NS, 'circle');
                    eye.setAttribute('cx', (headPt.x + headNorm.x * eyeOffset * side).toFixed(3));
                    eye.setAttribute('cy', (headPt.y + headNorm.y * eyeOffset * side).toFixed(3));
                    eye.setAttribute('r', '0.035');
                    eye.setAttribute('fill', '#1c1917');
                    eye.setAttribute('opacity', opacity);
                    layer.appendChild(eye);
                });
            },

            drawLadderCurve(from, to, layer = this.previewLayerEl, muted = false) {
                const x1 = from.col - 0.5, y1 = from.row - 0.5;
                const x2 = to.col - 0.5, y2 = to.row - 0.5;
                const dx = x2 - x1, dy = y2 - y1;
                const len = Math.max(Math.sqrt(dx * dx + dy * dy), 0.001);
                const ux = dx / len, uy = dy / len;
                const px = -uy, py = ux;
                const offset = 0.13;
                const rail1 = [x1 + px * offset, y1 + py * offset, x2 + px * offset, y2 + py * offset];
                const rail2 = [x1 - px * offset, y1 - py * offset, x2 - px * offset, y2 - py * offset];
                const rungCount = Math.max(Math.round(len / 0.45), 2);
                const opacity = muted ? '0.35' : '1';

                const addLine = (x1, y1, x2, y2, color, width) => {
                    const line = document.createElementNS(SVG_NS, 'line');
                    line.setAttribute('x1', x1); line.setAttribute('y1', y1);
                    line.setAttribute('x2', x2); line.setAttribute('y2', y2);
                    line.setAttribute('stroke', color);
                    line.setAttribute('stroke-width', width);
                    line.setAttribute('stroke-linecap', 'round');
                    line.setAttribute('opacity', opacity);
                    layer.appendChild(line);
                };

                addLine(rail1[0], rail1[1], rail1[2], rail1[3], '#92400E', '0.08');
                addLine(rail2[0], rail2[1], rail2[2], rail2[3], '#92400E', '0.08');

                for (let i = 0; i <= rungCount; i++) {
                    const t = i / rungCount;
                    addLine(
                        rail1[0] + (rail1[2] - rail1[0]) * t, rail1[1] + (rail1[3] - rail1[1]) * t,
                        rail2[0] + (rail2[2] - rail2[0]) * t, rail2[1] + (rail2[3] - rail2[1]) * t,
                        '#D97706', '0.06'
                    );
                }
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
