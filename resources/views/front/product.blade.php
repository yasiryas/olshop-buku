<x-layout-front title="Product - Wigati Buku"
    description="Jelajahi koleksi buku lengkap di Wigati Buku — novel, buku pelajaran, buku anak, hingga komik dengan harga bersahabat.">
    {{-- herosection --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Produk</h4>
            <p class="text-lg mb-8 text-gray-600">Temukan buku favoritmu di Wigati Buku</p>
        </div>
    </section>
    {{-- product section --}}
    <section class="py-12 md:py-20 bg-gray-100 px-4 md:px-10" id="products">
        <div class="container mx-auto flex flex-col gap-10">
            <div class="w-full max-w-xl mx-auto">
                <form action="{{ route('front.search') }}" method="GET" id="searchForm" class="w-full"
                    x-data="searchableList('{{ route('front.product') }}', 'results-product-grid')"
                    @submit.prevent="search()">
                    <input type="text" name="search" id="searchProduct" x-model="keyword"
                        @input.debounce.500ms="search()"
                        style="background-image: url('{{ asset('/assets/svgs/ic-search.svg') }}')"
                        class="block w-full py-3.5 pl-4 pr-10 rounded-[50px] font-semibold placeholder:text-grey placeholder:font-normal text-black text-base bg-no-repeat bg-[calc(100%-16px)]  focus:ring-2 focus:ring-primary focus:outline-none focus:border-none transition-all hover:ring-2 hover:ring-red-600"
                        placeholder="Cari buku favoritmu...">
                </form>
            </div>

            <div id="results-product-grid">
                @include('front.partials.products_grid')
            </div>
        </div>
    </section>
    {{-- Section CTA --}}
    <section>
        <div class="py-20 px-10 bg-white container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Ingin tahu lebih banyak tentang Wigati
                Buku?
            </p>
            <div class="text-center">
                <a href="{{ route('front.about') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-full transition">
                    About Us
                </a>
            </div>
        </div>
    </section>
</x-layout-front>
