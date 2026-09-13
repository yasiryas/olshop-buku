@php
    $metaKeywords = ($product->category->name ?? 'buku') . ', buku, toko buku online, Wigati Buku';
@endphp

<x-layout-front
    :title="$product->name . ' - Wigati Buku'"
    :description="strip_tags(Str::limit($product->about, 160))"
    :keywords="$metaKeywords"
    :image="$product->photo"
    :canonical="route('front.product.details', $product->slug)">
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => Storage::url($product->photo),
            'description' => Str::limit(strip_tags($product->about), 160),
            'brand' => ['@type' => 'Brand', 'name' => config('app.name')],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('front.product.details', $product->slug),
                'priceCurrency' => 'IDR',
                'price' => $product->price,
                'availability' => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    {{-- detail product section --}}

    <section class="container mx-auto px-4 md:px-10 mb-12 md:mb-20 pt-6 md:pt-10 relative">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white p-6 md:p-8 rounded-lg shadow-lg">
            <div>
                <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}"
                    class="w-full h-72 md:h-96 object-cover mb-4 rounded">
            </div>
            <div class="prose max-w-none">
                <h1 class="text-2xl md:text-4xl font-bold mb-4 text-gray-700">{{ $product->name }}</h1>
                <p class="text-base md:text-lg mb-4 text-gray-600">Category: {{ $product->category->name }}
                </p>
                <h2 class="text-2xl text-red-600 font-bold mb-2"> Rp {{ number_format($product->price) }}
                </h2>
                {!! $product->about !!}
                <p class="text-gray-600 mb-4">Tersedia: {{ $product->stock }}</p>
                <form action="{{ route('carts.add', $product->id) }}" method="POST">
                    @csrf
                    @if ($product->stock < 1)
                        <button type="submit" disabled
                            class="bg-gray-400 cursor-not-allowed text-white font-semibold py-3 px-8 rounded-full transition mt-4"><i
                                class="fas fa-shopping-cart mr-2"></i>Stok Habis</button>
                    @else
                        <button type="submit" name="product_id" value="{{ $product->id }}"
                            class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full transition mt-4"><i
                                class="fas fa-shopping-cart mr-2"></i>Add
                            To Cart</button>
                    @endif
                </form>
            </div>
        </div>
    </section>
    {{-- end article section --}}
    {{-- latest product section --}}
    <section class="py-12 md:py-20 px-4 md:px-10 bg-gray-100">
        <div class="container mx-auto ">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-8 md:mb-10">Latest Product</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @forelse ($products as $product)
                    <div class=" bg-white p-6 rounded-lg shadow-lg text-center hover:scale-105 transition">
                        <a href="{{ route('front.product.details', $product->slug) }}"
                            class="hover:scale-105 transition">
                            <img src=" {{ Storage::url($product->photo) }} " alt="{{ $product->name }}"
                                class="w-full h-48 object-cover mb-4 rounded">
                            <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                            <span class="text-red-600 font-bold mb-2"> Rp {{ number_format($product->price) }}
                            </span>
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
                                        class="fas fa-shopping-cart mr-2"></i>Add
                                    To Cart</button>
                            @endif
                        </form>
                    </div>
                @empty
                    <div class="col-span-full bg-white p-6 rounded-lg shadow-lg text-center">
                        <p>Ups, Tidak ada produk</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layout-front>
