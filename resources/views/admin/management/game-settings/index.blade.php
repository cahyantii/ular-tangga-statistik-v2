<x-admin-layout>
    <h1 class="mb-6 text-2xl font-bold text-slate-900">Kelola Game Settings</h1>

    <form method="POST" action="{{ route('admin.management.game-settings.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        @foreach ($settingsByGroup as $group => $settings)
            <div class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ $group }}</h2>

                <div class="space-y-4">
                    @foreach ($settings as $setting)
                        <div class="grid grid-cols-1 gap-2 border-b border-slate-100 pb-4 last:border-0 last:pb-0 sm:grid-cols-3 sm:items-start">
                            <div class="sm:col-span-2">
                                <label for="setting-{{ $setting->id }}" class="block text-sm font-medium text-slate-700">{{ $setting->label }}</label>
                                @if ($setting->deskripsi)
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $setting->deskripsi }}</p>
                                @endif
                                @error("settings.{$setting->id}.value")
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <input type="hidden" name="settings[{{ $setting->id }}][_version]" value="{{ $setting->updated_at->timestamp }}">

                                @if ($setting->type->value === 'boolean')
                                    <input type="hidden" name="settings[{{ $setting->id }}][value]" value="0">
                                    <input type="checkbox" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="1"
                                           @checked($setting->value === '1')
                                           class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                @elseif ($setting->type->value === 'integer')
                                    <input type="number" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="{{ old("settings.{$setting->id}.value", $setting->value) }}"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @else
                                    <input type="text" id="setting-{{ $setting->id }}"
                                           name="settings[{{ $setting->id }}][value]" value="{{ old("settings.{$setting->id}.value", $setting->value) }}"
                                           class="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Simpan Semua Perubahan
            </button>
        </div>
    </form>
</x-admin-layout>
