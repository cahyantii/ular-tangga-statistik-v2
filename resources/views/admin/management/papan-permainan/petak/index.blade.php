@php
    $boardConfig = [
        'jumlah_kolom' => $papan->jumlah_kolom,
        'jumlah_petak' => $papan->jumlah_petak,
        'petak' => $papan->petak->map(fn ($p) => [
            'id' => $p->id,
            'posisi' => $p->posisi,
            'jenis_petak' => $p->jenis_petak->value,
            'is_active' => $p->is_active,
            'kategori_id' => $p->kategori_id,
            'label' => $p->label,
            'icon' => $p->icon,
            'warna' => $p->warna,
            'border_warna' => $p->border_warna,
            'deskripsi' => $p->deskripsi,
            'version' => (string) $p->updated_at->timestamp,
        ])->values(),
        'konektor' => $papan->papanKonektor->map(fn ($k) => [
            'posisi_awal' => $k->posisi_awal,
            'posisi_akhir' => $k->posisi_akhir,
        ])->values(),
    ];

    $jenisOptions = [
        'biasa'   => 'Petak Biasa',
        'mystery' => 'Mystery',
    ];

    $legend = [
        'start'   => ['label' => 'Start',   'color' => '#059669'],
        'finish'  => ['label' => 'Finish',  'color' => '#7c3aed'],
        'biasa'   => ['label' => 'Biasa',   'color' => '#f1f5f9'],
        'tangga'  => ['label' => 'Tangga',  'color' => '#10b981'],
        'ular'    => ['label' => 'Ular',    'color' => '#e11d48'],
        'mystery' => ['label' => 'Mystery', 'color' => '#6366f1'],
    ];
@endphp

<x-admin-layout>
    @push('scripts')
        @vite(['resources/js/petak-editor.js'])
    @endpush

    <script>
    function petakPanel() {
        const TILE_COLORS = {
            start: '#059669', finish: '#7c3aed', biasa: '#f1f5f9',
            tangga: '#10b981', ular: '#e11d48', mystery: '#6366f1',
        };

        return {
            panelOpen: false,
            saving: false,
            error: null,
            blockedMessage: null,
            tile: null,
            form: { jenis_petak: 'biasa', is_active: true, kategori_id: '', label: '', icon: '', warna: '', border_warna: '', deskripsi: '', _version: '' },
            kategoriOptions: {},
            updateUrlBase: '',

            init() {
                const root = document.getElementById('board-editor-data');
                this.kategoriOptions = JSON.parse(root.dataset.kategoriOptions || '{}');
                this.updateUrlBase = root.dataset.updateUrlBase;

                window.addEventListener('petak:selected', (e) => this.open(e.detail.tile));
                window.addEventListener('petak:blocked', (e) => this.notifyBlocked(e.detail.tile));
            },

            open(tile) {
                this.tile = tile;
                this.form = {
                    jenis_petak: tile.jenis_petak,
                    is_active: tile.is_active ?? true,
                    kategori_id: tile.kategori_id ?? '',
                    label: tile.label ?? '',
                    icon: tile.icon ?? '',
                    warna: tile.warna ?? '',
                    border_warna: tile.border_warna ?? '',
                    deskripsi: tile.deskripsi ?? '',
                    _version: tile.version,
                };
                this.error = null;
                this.panelOpen = true;
                this.highlight(tile.posisi);
            },

            close() {
                this.panelOpen = false;
                this.clearHighlight();
                this.tile = null;
            },

            notifyBlocked(tile) {
                this.blockedMessage = ['start', 'finish'].includes(tile.jenis_petak)
                    ? 'Petak Start/Finish tidak bisa diubah.'
                    : 'Petak ini dikelola lewat halaman Konektor. Hapus konektornya dulu untuk mengubah petak ini.';
                setTimeout(() => { this.blockedMessage = null; }, 3500);
            },

            highlight(posisi) {
                this.clearHighlight();
                const cell = document.querySelector('[data-posisi="' + posisi + '"]');
                if (cell) {
                    cell.style.outline = '3px solid #0ea5e9';
                    cell.style.outlineOffset = '1px';
                }
            },

            clearHighlight() {
                document.querySelectorAll('.board-tile').forEach((el) => { el.style.outline = ''; });
            },

            livePatch() {
                if (!this.tile) {
                    return;
                }
                const cell = document.querySelector('[data-posisi="' + this.tile.posisi + '"]');
                if (!cell) {
                    return;
                }
                const effectiveJenis = this.form.is_active ? this.form.jenis_petak : 'biasa';
                cell.style.backgroundColor = (this.form.is_active && this.form.warna) ? this.form.warna : TILE_COLORS[effectiveJenis] || TILE_COLORS.biasa;
                cell.style.border = this.form.is_active ? (this.form.border_warna || '') : '';
                cell.style.opacity = this.form.is_active ? '1' : '0.45';

                // renderBoardGrid() only ever creates a posisi-number span plus, optionally,
                // a jenis-label span and a connector-arrow span. Drop everything but the
                // posisi number and replace it with a single up-to-date live label so stale
                // text (e.g. the old jenis) can't linger alongside the new one.
                Array.from(cell.querySelectorAll('span')).slice(1).forEach((el) => el.remove());

                const labelEl = document.createElement('span');
                labelEl.className = 'live-label text-[10px] font-normal opacity-90';
                labelEl.textContent = !this.form.is_active
                    ? 'nonaktif'
                    : (this.form.label || (this.form.jenis_petak !== 'biasa' ? this.form.jenis_petak : ''));
                cell.appendChild(labelEl);
            },

            async save() {
                this.saving = true;
                this.error = null;

                try {
                    const response = await fetch(this.updateUrlBase + '/' + this.tile.id, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(this.form),
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        this.error = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Gagal menyimpan petak.');
                        this.saving = false;
                        return;
                    }

                    Object.assign(this.tile, data.petak, { version: data.version });
                    this.saving = false;
                    this.close();
                } catch (e) {
                    this.error = 'Gagal terhubung ke server. Coba lagi.';
                    this.saving = false;
                }
            },
        };
    }
    </script>

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Editor Petak — {{ $papan->nama }}</h1>
            <p class="mt-1 text-sm text-slate-500">Klik sebuah petak untuk mengubah jenis, warna, border, label, dan deskripsinya.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.management.papan-permainan.petak.import.create', $papan) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-violet-200 px-3 py-2 text-xs font-semibold text-violet-600 hover:bg-violet-50">
                <x-player.icon name="upload-cloud" class="h-3.5 w-3.5" />
                Import Petak
            </a>
            <a href="{{ route('admin.management.papan-permainan.petak.export', $papan) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-orange-200 px-3 py-2 text-xs font-semibold text-orange-600 hover:bg-orange-50">
                <x-player.icon name="download" class="h-3.5 w-3.5" />
                Export Petak
            </a>
            <a href="{{ route('admin.management.papan-permainan.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-600 hover:text-slate-900">
                <x-player.icon name="arrow-right" class="h-4 w-4 rotate-180" />
                Kembali ke daftar papan
            </a>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap gap-3 rounded-2xl border border-slate-100 bg-white p-4 text-xs shadow-[0_10px_40px_rgba(0,0,0,.06)]">
        @foreach ($legend as $meta)
            <span class="inline-flex items-center gap-1.5">
                <span class="h-3 w-3 rounded" style="background-color: {{ $meta['color'] }}"></span>
                {{ $meta['label'] }}
            </span>
        @endforeach
    </div>

    <div x-data="petakPanel()" class="relative">
        <div id="board-grid" class="rounded-[26px] border border-slate-100 bg-white p-4 shadow-[0_10px_40px_rgba(0,0,0,.06)]"></div>

        <div id="board-editor-data"
             data-board="{{ json_encode($boardConfig) }}"
             data-kategori-options="{{ json_encode($kategoriOptions) }}"
             data-update-url-base="{{ route('admin.management.papan-permainan.petak.index', $papan) }}"></div>

        {{-- Toast for blocked tiles (Start/Finish/Tangga/Ular) --}}
        <div x-show="blockedMessage" x-transition x-cloak
             class="fixed bottom-6 left-1/2 z-40 -translate-x-1/2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-lg">
            <span x-text="blockedMessage"></span>
        </div>

        {{-- Backdrop --}}
        <div x-show="panelOpen" x-transition.opacity x-cloak @click="close()" class="fixed inset-0 z-40 bg-slate-900/30"></div>

        {{-- Side panel --}}
        <div x-show="panelOpen" x-transition x-cloak
             class="fixed inset-y-0 right-0 z-50 flex w-full max-w-md flex-col overflow-y-auto bg-white p-6 shadow-2xl">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-lg font-bold text-slate-900">Edit Petak <span x-text="tile ? '#' + tile.posisi : ''"></span></h2>
                <button type="button" @click="close()" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <x-player.icon name="close" class="h-5 w-5" />
                </button>
            </div>

            <template x-if="error">
                <p class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-xs font-medium text-red-600" x-text="error"></p>
            </template>

            <div class="space-y-4">
                <label class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2.5">
                    <span class="text-sm font-medium text-slate-700">
                        Petak Aktif
                        <span class="block text-xs font-normal text-slate-400">Nonaktif = efek petak ini dimatikan sementara, diperlakukan seperti petak biasa di gameplay.</span>
                    </span>
                    <input type="checkbox" x-model="form.is_active" @change="livePatch()"
                           class="h-5 w-5 shrink-0 rounded border-slate-300 text-green-600 focus:ring-green-500">
                </label>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Petak</label>
                    <select x-model="form.jenis_petak" @change="livePatch()"
                            class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                        @foreach ($jenisOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>


                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Label</label>
                    <input type="text" x-model="form.label" @input="livePatch()"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Icon</label>
                    <input type="text" x-model="form.icon" placeholder="nama-icon (opsional)"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Warna (hex atau CSS gradient)</label>
                    <input type="text" x-model="form.warna" @input="livePatch()" placeholder="#2563eb atau linear-gradient(...)"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Border</label>
                    <input type="text" x-model="form.border_warna" @input="livePatch()" placeholder="3px solid #f59e0b"
                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <textarea x-model="form.deskripsi" rows="3"
                              class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-green-500 focus:ring-green-500"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" @click="close()" class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Batal</button>
                <button type="button" @click="save()" :disabled="saving"
                        class="admin-nav-active rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-60">
                    <span x-show="!saving">Simpan</span>
                    <span x-show="saving" x-cloak>Menyimpan&hellip;</span>
                </button>
            </div>
        </div>
    </div>
</x-admin-layout>
