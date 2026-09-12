<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>
            <form action="{{ route('stocks.index') }}"
                x-data="searchableList('{{ route('stocks.index') }}', 'results-stocks')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Search stocks..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 text-slate-400 rounded-full px-4 py-2">
            </form>
            <a href="{{ route('stocks.allHistory') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">
                History Stock
            </a>
        </div>
    </x-slot>

    <div x-data="stockModal()" @keydown.escape.window="close()">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div id="results-stocks">
                    @include('admin.partials.stocks_list')
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <!-- Overlay -->
        <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-50" @click="close()">
        </div>

        <!-- Modal Box -->
        <div x-show="show"
            class="fixed bg-white w-full max-w-md p-6 rounded-lg shadow-xl z-[60] left-1/2 top-1/2
                    -translate-x-1/2 -translate-y-1/2">

            <button type="button" @click="close()" aria-label="Tutup"
                class="absolute top-3 right-3 p-1.5 rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <h2 class="text-xl font-bold text-gray-800 mb-4" x-text="title"></h2>

            <form method="POST" :action="actionUrl">
                @csrf
                <label class="block mb-2 text-sm font-semibold text-gray-700">Jumlah</label>
                <input type="number" name="quantity" class="w-full text-gray-400 border-gray-300 rounded-lg"
                    placeholder="Masukkan jumlah" required>
                <input type="hidden" name="type" :value="mode">

                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" @click="close()" class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full">
                        Submit
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- SCRIPT -->
    <x-slot name="script">
        <script>
            function stockModal() {
                return {
                    show: false,
                    productId: null,
                    mode: null,
                    actionUrl: '',
                    title: '',

                    openModal(id, mode) {
                        this.show = true;
                        this.productId = id;
                        this.mode = mode;

                        this.title = mode === 'in' ?
                            'Tambah Stock (Stock In)' :
                            'Kurangi Stock (Stock Out)';

                        this.actionUrl = `/admin/stocks/${id}/update`;
                    },

                    close() {
                        this.show = false;
                    }
                }
            }
        </script>
    </x-slot>

</x-app-layout>
