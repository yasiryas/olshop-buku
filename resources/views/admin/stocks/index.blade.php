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

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
                @forelse($products as $product)
                    <div class="item-card flex flex-row justify-between items-center border-b pb-4">

                        {{-- Produk --}}
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

                        {{-- Kategori --}}
                        <p class="text-base text-slate-500 w-40">
                            {{ $product->category->name }}
                        </p>

                        {{-- Stok --}}
                        <div class="w-32 text-center">
                            <p class="text-xs text-slate-500">Stok Saat Ini</p>
                            <p class="text-xl font-bold text-green-600">
                                {{ $product->stock }}
                            </p>
                        </div>

                        {{-- Aksi --}}
                        <div class="flex flex-row items-center gap-x-3">
                            <a href="{{ route('stocks.history', $product) }}"
                                class="font-bold py-2 px-4 rounded-full text-white bg-slate-600">History</a>

                            <a href="{{ route('stocks.show', $product) }}?mode=in"
                                class="font-bold py-2 px-4 rounded-full text-white bg-green-600">Stock In</a>

                            <a href="{{ route('stocks.show', $product) }}?mode=out"
                                class="font-bold py-2 px-4 rounded-full text-white bg-red-600">Stock Out</a>
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
</x-app-layout>
