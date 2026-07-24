<x-admin-layout>
    @php
        $groupMeta = [
            'Skor' => ['title' => 'Skor & Penilaian', 'subtitle' => 'Atur poin dan sistem penilaian dalam permainan', 'icon' => 'trophy'],
            'Timer' => ['title' => 'Timer & Waktu', 'subtitle' => 'Atur batas waktu dan timer dalam permainan', 'icon' => 'clock'],
        ];

        $itemMeta = [
            'wrong_answer_penalty' => ['icon' => 'help', 'bg' => '#ECFDF5', 'fg' => '#10B981'],
            'tile_penalty_point' => ['icon' => 'star', 'bg' => '#EFF6FF', 'fg' => '#3B82F6'],
            'bonus_point' => ['icon' => 'gift', 'bg' => '#F3E8FF', 'fg' => '#8B5CF6'],
            'correct_answer_point' => ['icon' => 'check-circle', 'bg' => '#FEF3C7', 'fg' => '#F59E0B'],
            'win_point' => ['icon' => 'flag', 'bg' => '#FEE2E2', 'fg' => '#EF4444'],
            'heartbeat_timeout_seconds' => ['icon' => 'heartbeat', 'bg' => '#ECFDF5', 'fg' => '#10B981'],
            'question_timer_seconds' => ['icon' => 'clock', 'bg' => '#EFF6FF', 'fg' => '#3B82F6'],
            'reconnect_timeout_seconds' => ['icon' => 'wifi', 'bg' => '#F3E8FF', 'fg' => '#8B5CF6'],
            'room_waiting_expiry_minutes' => ['icon' => 'hourglass', 'bg' => '#FEF3C7', 'fg' => '#F59E0B'],
        ];
        $fallbackPalette = [
            ['icon' => 'settings', 'bg' => '#ECFDF5', 'fg' => '#10B981'],
            ['icon' => 'settings', 'bg' => '#EFF6FF', 'fg' => '#3B82F6'],
            ['icon' => 'settings', 'bg' => '#F3E8FF', 'fg' => '#8B5CF6'],
            ['icon' => 'settings', 'bg' => '#FEF3C7', 'fg' => '#F59E0B'],
            ['icon' => 'settings', 'bg' => '#FEE2E2', 'fg' => '#EF4444'],
        ];
    @endphp

    {{-- Page header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-extrabold leading-tight text-[#1E293B] sm:text-[42px]">Game Settings</h1>
            <p class="mt-1 text-base text-[#64748B] sm:text-lg">Kelola konfigurasi permainan untuk pengalaman bermain yang lebih optimal</p>
        </div>

        <button type="submit" form="game-settings-form"
                class="inline-flex h-14 shrink-0 items-center justify-center gap-2 self-start rounded-[18px] px-7 text-sm font-semibold text-white shadow-[0_10px_25px_rgba(34,197,94,.28)] transition duration-[0.25s] ease-in-out hover:-translate-y-0.5 hover:brightness-110 hover:shadow-[0_14px_32px_rgba(34,197,94,.36)] sm:self-auto"
                style="background: linear-gradient(90deg, #22c55e, #16a34a);">
            <x-player.icon name="save" class="h-5 w-5" />
            Simpan Semua Perubahan
        </button>
    </div>

    <form id="game-settings-form" method="POST" action="{{ route('admin.management.game-settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($settingsByGroup as $group => $settings)
            @php
                $meta = $groupMeta[$group] ?? ['title' => $group, 'subtitle' => null, 'icon' => 'settings'];
            @endphp
            <div class="rounded-[28px] border border-[#E5E7EB]/70 bg-white p-8 shadow-[0_10px_40px_rgba(0,0,0,.06)]">
                <div class="mb-6 flex items-center gap-4">
                    <span class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-2xl bg-[#ECFDF5] text-[#10B981]">
                        <x-player.icon :name="$meta['icon']" class="h-6 w-6" />
                    </span>
                    <div>
                        <h2 class="text-sm font-bold uppercase tracking-wider text-[#10B981]">{{ $meta['title'] }}</h2>
                        @if ($meta['subtitle'])
                            <p class="mt-0.5 text-sm text-[#64748B]">{{ $meta['subtitle'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    @foreach ($settings as $index => $setting)
                        @php
                            $colors = $itemMeta[$setting->key] ?? $fallbackPalette[$index % count($fallbackPalette)];
                            $icon = $itemMeta[$setting->key]['icon'] ?? $colors['icon'];
                        @endphp
                        <div class="flex flex-col gap-4 rounded-[18px] border border-[#E5E7EB] bg-white p-5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex min-w-0 items-center gap-4">
                                <span class="flex h-[60px] w-[60px] shrink-0 items-center justify-center rounded-2xl" style="background: {{ $colors['bg'] }}; color: {{ $colors['fg'] }};">
                                    <x-player.icon :name="$icon" class="h-7 w-7" />
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-[#1F2937]">{{ $setting->label }}</p>
                                    @if ($setting->deskripsi)
                                        <p class="mt-0.5 truncate text-sm text-[#64748B]">{{ $setting->deskripsi }}</p>
                                    @endif
                                    @error("settings.{$setting->id}.value")
                                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="shrink-0">
                                <input type="hidden" name="settings[{{ $setting->id }}][_version]" value="{{ $setting->updated_at->timestamp }}">

                                @if ($setting->type->value === 'boolean')
                                    <input type="hidden" name="settings[{{ $setting->id }}][value]" value="0">
                                    <input type="checkbox" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="1"
                                           @checked($setting->value === '1')
                                           class="h-5 w-5 rounded border-[#E5E7EB] text-[#10B981] focus:ring-[#10B981]">
                                @elseif ($setting->type->value === 'integer')
                                    <input type="number" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="{{ old("settings.{$setting->id}.value", $setting->value) }}"
                                           class="h-12 w-full rounded-xl border-[#E5E7EB] text-center text-base font-bold text-[#1E293B] shadow-sm transition duration-200 ease-in-out focus:border-[#10B981] focus:shadow-[0_0_0_3px_rgba(16,185,129,.15)] focus:ring-[#10B981] sm:w-[110px]">
                                @else
                                    <input type="text" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="{{ old("settings.{$setting->id}.value", $setting->value) }}"
                                           class="h-12 w-full rounded-xl border-[#E5E7EB] text-center text-base font-bold text-[#1E293B] shadow-sm transition duration-200 ease-in-out focus:border-[#10B981] focus:shadow-[0_0_0_3px_rgba(16,185,129,.15)] focus:ring-[#10B981] sm:w-[110px]">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex items-start gap-3 rounded-[18px] border border-[#FDE68A] bg-[#FEF3C7] p-5">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center text-[#F59E0B]">
                <x-player.icon name="info" class="h-6 w-6" />
            </span>
            <div>
                <p class="font-bold text-[#B45309]">Informasi</p>
                <p class="mt-0.5 text-sm text-[#92400E]">Perubahan pengaturan akan berlaku untuk semua permainan baru. Permainan yang sedang berlangsung tidak akan terpengaruh.</p>
            </div>
        </div>
    </form>
</x-admin-layout>
