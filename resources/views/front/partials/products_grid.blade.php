<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-center">
    @forelse ($products as $product)
        <div class=" bg-white p-6 rounded-lg shadow-lg text-center hover:scale-105 transition">
            <a href="{{ route('front.product.details', $product->slug) }}"
                class="hover:scale-105 transition">
                <img src=" {{ Storage::url($product->photo) }} " alt="{{ $product->name }}"
                    class="w-full h-48 object-cover mb-4 rounded">
                <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                <span class="text-red-600 font-bold mb-4"> Rp {{ number_format($product->price) }}
                </span>
                <p class="text-gray-600 mb-4">Tersedia: {{ $product->stock }}</p>
            </a>
            <form action="{{ route('carts.add', $product->id) }}" method="POST">
                @csrf
                @if ($product->stock < 1)
                    <button type="submit" disabled
                        class="bg-gray-400 cursor-not-allowed text-white font-semibold py-3 px-8 rounded-full transition mt-4"><i
                            class="fas fa-shopping-cart mr-2"></i>Stok Habis</button>
                @else
                    <button type="submit" name="product_id" value="{{ $product->id }}"
                        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full transition mt-4"><i
                            class="fas fa-shopping-cart mr-2"></i>Tambah ke Keranjang</button>
                @endif
            </form>
        </div>
    @empty
        <div class="col-span-full bg-white p-6 rounded-lg shadow-lg text-center">
            <p>Ups, Tidak ada produk</p>
        </div>
    @endforelse

    @if (isset($products) && method_exists($products, 'hasPages') && $products->hasPages())
        <div class="col-span-full mt-8">
            {{ $products->links() }}
        </div>
    @endif
</div>