@props(['label', 'name', 'checked' => false])

<div class="flex items-center gap-2">
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="checkbox"
        value="1"
        @checked(old($name, $checked))
        {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500']) }}
    >
    <label for="{{ $name }}" class="text-sm font-medium text-slate-700">{{ $label }}</label>

    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
