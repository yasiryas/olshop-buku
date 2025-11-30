<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Stocks') }}
            </h2>

            <a href="{{ route('admin.products.index') }}"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">
                Back to Products
            </a>
        </div>
    </x-slot>

    <div x-data="stockModal()">

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white flex flex-col gap-y-5 p-10 shadow-sm sm:rounded-lg">

                    @forelse($products as $product)
                        <div class="item-card flex flex-row justify-between items-center border-b pb-4">

                            <div class="flex flex-row items-center gap-x-3 w-64">
                                <img src="{{ Storage::url($product->photo) }}"
                                    class="w-[60px] h-[60px] rounded object-cover">

                                <div>
                                    <h3 class="text-lg font-bold text-indigo-900">{{ $product->name }}</h3>
                                    <p class="text-sm text-slate-500">
                                        Rp {{ number_format($product->price) }}
                                    </p>
                                </div>
                            </div>

                            <p class="w-40 text-base text-slate-500">
                                {{ $product->category->name }}
                            </p>

                            <div class="w-32 text-center">
                                <p class="text-xs text-slate-500">Stok Saat Ini</p>
                                <p class="text-xl font-bold text-green-600">
                                    {{ $product->stock }}
                                </p>
                            </div>

                            <div class="flex gap-x-3">
                                <button @click="openModal('{{ $product->id }}','in')"
                                    class="font-bold py-2 px-4 rounded-full text-white bg-green-600">
                                    Stock In
                                </button>

                                <button @click="openModal('{{ $product->id }}','out')"
                                    class="font-bold py-2 px-4 rounded-full text-white bg-red-600">
                                    Stock Out
                                </button>
                            </div>

                        </div>

                    @empty
                        <p class="text-center text-slate-600">
                            Ups, belum ada produk. <b>Coba tambahkan produk terlebih dahulu!</b>
                        </p>
                    @endforelse

                </div>
            </div>
        </div>

        <!-- MODAL -->
        <!-- Overlay -->
        <div x-show="show" x-cloak x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 z-50"
            @click="close()">
        </div>

        <!-- Modal Box -->
        <div x-show="show" x-cloak x-transition @click.stop
            class="fixed bg-white w-full max-w-md p-6 rounded-lg shadow-xl z-[60] left-1/2 top-1/2
                    -translate-x-1/2 -translate-y-1/2">

            <h2 class="text-xl font-bold text-gray-800 mb-4" x-text="title"></h2>

            <form method="POST" :action="actionUrl">
                @csrf

                <label class="block mb-2 text-sm font-semibold text-gray-700">Jumlah</label>
                <input type="number" name="quantity" class="w-full border-gray-300 rounded-lg"
                    placeholder="Masukkan jumlah" required>

                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" @click="close()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                        Cancel
                    </button>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
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

                        this.actionUrl = `/admin/stocks/${id}/update?mode=${mode}`;
                    },

                    close() {
                        this.show = false;
                    }
                }
            }
        </script>
    </x-slot>

</x-app-layout>
