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
                <p class="text-xl font-bold text-indigo-600">
                    {{ $product->stock }}
                </p>
            </div>

            <div class="flex gap-x-3">
                <button @click="openModal('{{ $product->id }}','in')"
                    class="font-semibold py-1.5 px-3 rounded-full text-white bg-blue-600 hover:bg-blue-700">
                    Stock In
                </button>

                <button @click="openModal('{{ $product->id }}','out')"
                    class="font-bold py-2 px-4 rounded-full text-white bg-yellow-400 hover:bg-yellow-500">
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
<div class="mt-5">
    {{ $products->appends(['search' => $search])->links() }}
</div>