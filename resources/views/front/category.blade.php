<x-layout-front title="Product - Wigati Buku">
    {{-- Section Header --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Kategori {{ $category->name }}</h4>
        </div>
    </section>
    {{-- product section --}}
    <section class="py-20 bg-gray-100 px-10" id="products">
        <div class="container mx-auto flex flex-col gap-10">
            <div class="w-1/2 mx-auto">
                <form action="{{ route('front.search') }}" method="GET" id="searchForm" class="w-full">
                    <input type="text" name="search" id="searchProduct"
                        style="background-image: url('{{ asset('/assets/svgs/ic-search.svg') }}')"
                        class="block w-full py-3.5 pl-4 pr-10 rounded-[50px] font-semibold placeholder:text-grey placeholder:font-normal text-black text-base bg-no-repeat bg-[calc(100%-16px)]  focus:ring-2 focus:ring-primary focus:outline-none focus:border-none transition-all hover:ring-2 hover:ring-red-600"
                        placeholder="Cari buku faforitmu...">
                </form>
            </div>

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
                        </a>
                        <form action="{{ route('carts.add', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit" name="product_id" value="{{ $product->id }}"
                                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded transition mt-4"><i
                                    class="fas fa-shopping-cart mr-2"></i>Add
                                To Cart</button>
                        </form>
                    </div>

                @empty
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <p>Ups, Tidak ada produk</p>
                    </div>
                @endforelse
            </div>
        </div>
        </div>
    </section>

</x-layout-front>
