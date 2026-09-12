<div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
    @forelse($products as $product)
        <div class="item-card flex flex-row justify-between items-center">
            <div class="flex flex-row items-center gap-x-3">
                <img src="{{ Storage::url($product->photo) }}" alt="" class="w-[50px] h-[50px]">
                <div>
                    <h3 class="text-xl font-bold text-indigo-900">{{ $product->name }}</h3>
                    <p class="text-base text-slate-500">
                        Rp. {{ number_format($product->price) }} ·
                        {{ $product->category->name }}
                    </p>
                </div>
            </div>
            <div class="flex flex-row items-center gap-x-3">
                <button type="button" @click="openEdit('{{ $product->id }}')"
                    class="font-bold py-3 px-5 rounded-full text-white bg-yellow-500">Edit</button>
                <button type="button" @click="openDelete('{{ $product->id }}')"
                    class="font-semibold py-2 px-4 rounded-full text-white bg-red-700">Delete</button>
            </div>
        </div>
    @empty
        <p>
            Ups, belum ada produk nih, <b>coba lagi nanti ya!</b>
        </p>
    @endforelse
</div>
<div class="mt-5">
    {{ $products->appends(['search' => $search])->links() }}
</div>