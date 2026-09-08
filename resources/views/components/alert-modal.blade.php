<div x-data="{ show: false, message: '', type: 'info' }" x-show="show" x-cloak
    @show-alert.window="
        message = $event.detail.message;
        type = $event.detail.type ?? 'info';
        show = true;
        setTimeout(() => show = false, 2500);
    "
    @keydown.escape.window="show = false"
    class="fixed inset-0 flex items-center justify-center z-[9999]">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black bg-opacity-50" @click="show = false"></div>

    <!-- Modal -->
    <div x-transition.opacity x-transition.scale.80
        class="relative bg-white w-80 rounded-2xl shadow-lg p-6 z-10 text-center">
        <!-- CLOSE -->
        <button type="button" @click="show = false" aria-label="Tutup"
            class="absolute top-3 right-3 p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <!-- ICON -->
        <template x-if="type === 'error'">
            <div class="text-red-600 text-5xl mb-2">
                <i class="fas fa-circle-xmark"></i>
            </div>
        </template>

        <template x-if="type === 'success'">
            <div class="text-green-600 text-5xl mb-2">
                <i class="fas fa-circle-check"></i>
            </div>
        </template>

        <template x-if="type === 'warning'">
            <div class="text-yellow-500 text-5xl mb-2">
                <i class="fas fa-triangle-exclamation"></i>
            </div>
        </template>

        <template x-if="type === 'info'">
            <div class="text-blue-500 text-5xl mb-2">
                <i class="fas fa-circle-info"></i>
            </div>
        </template>

        <!-- MESSAGE -->
        <p class="text-gray-700 font-semibold" x-text="message"></p>

        <!-- BUTTON -->
        <button @click="show = false" class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
            OK
        </button>
    </div>
</div>
