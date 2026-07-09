@props(['name'])

<template x-teleport="body">
    <div
        x-data="{ open: false }"
        x-show="open"
        x-cloak
        @open-modal.window="if ($event.detail === '{{ $name }}') open = true"
        @close-modal.window="open = false"
        @keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="open = false"
            class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative w-full max-w-lg rounded-3xl bg-white p-6 shadow-soft-lg"
        >
            <button @click="open = false" class="absolute right-4 top-4 rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                <x-player.icon name="close" class="h-5 w-5" />
            </button>

            {{ $slot }}
        </div>
    </div>
</template>
