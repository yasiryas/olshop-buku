@props([
    'title' => 'Yakin ingin melanjutkan?',
    'message' => null,
    'confirmText' => 'Ya, Lanjutkan',
    'confirmClass' => 'bg-red-600 text-white',
    'icon' => 'fa-triangle-exclamation',
    'action' => null,
    'method' => 'DELETE',
])

<div x-data="{ show: false }" @keydown.escape.window="show = false" class="inline">
    <button type="button" @click.stop="show = true" {{ $attributes->merge(['class' => '']) }}>
        {{ $slot }}
    </button>

    <div x-show="show" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center">
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="show = false"></div>
        <div x-transition class="relative bg-white rounded-2xl shadow-lg p-6 w-80 text-center z-10">
            <button type="button" @click="show = false" aria-label="Tutup"
                class="absolute top-3 right-3 p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <div class="text-red-600 text-4xl mb-3">
                <i class="fas {{ $icon }}"></i>
            </div>
            <p class="font-bold text-lg text-gray-800">{{ $title }}</p>
            @if ($message)
                <p class="text-sm text-gray-500 mt-1">{{ $message }}</p>
            @endif
            <div class="flex justify-center gap-3 mt-5">
                <button type="button" @click="show = false"
                    class="px-5 py-2 bg-gray-200 text-gray-700 rounded-full font-semibold hover:bg-gray-300">
                    Batal
                </button>
                <form method="POST" action="{{ $action }}">
                    @csrf
                    @method($method)
                    <button type="submit"
                        class="px-5 py-2 {{ $confirmClass }} rounded-full font-semibold hover:opacity-90">
                        {{ $confirmText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
</style>