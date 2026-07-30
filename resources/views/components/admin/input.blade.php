@props(['label', 'name', 'type' => 'text', 'value' => null, 'required' => false])

<div>
    <label for="{{ $name }}" class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
        {{ $label }} @if ($required)<span class="text-red-500 dark:text-red-400">*</span>@endif
    </label>

    @if ($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100 dark:placeholder-slate-500']) }}
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'file')
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="file"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100']) }}
        >
    @else
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm dark:bg-slate-800 dark:border-slate-600 dark:text-slate-100 dark:placeholder-slate-500']) }}
        >
    @endif

    @error($name)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
