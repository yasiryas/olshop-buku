<x-layout-front title="Home - Wigati Buku">
    {{-- herosection --}}
    <section class=" py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 ">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Ribuan judul buku dari berbagai genre</h4>
            <p class="text-lg mb-8 text-gray-600">fiksi, nonfiksi, bisnis, pendidikan, hingga buku anak. Lengkap.
                Terjangkau. Cepat
                sampai</p>
            <a href="#products"
                @click.prevent="$el.closest('body').querySelector('#products').scrollIntoView({ behavior: 'smooth' })"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded">
                <i class="fa-solid fa-bag-shopping mr-2"></i> Shop Now
            </a>
        </div>
        <div class="container mx-auto w-3/6">
            <img src="{{ asset('/assets/images/herosection.webp') }}" alt="">
        </div>
    </section>

    {{-- Categories Section --}}
    <section class="py-10 bg-gray-200 px-10 space-x-6">
        <div class="container mx-auto">
            <div id="categoriesSlider" class="relative flex justify-center">
                @forelse ($categories as $category)
                    <a href="{{ route('front.product.category', $category->id) }}" class="hover:rotate-6 transition">
                        <div class="inline-flex gap-2.5 items-center py-3 px-3.5 relative bg-white rounded-xl mr-4">
                            <img src="{{ Storage::url($category->icon) }}" class="size-10" alt="">
                            <p class="text-gray-600 font-semibold">
                                {{ $category->name }}
                            </p>
                        </div>
                    </a>
                @empty
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <p>Ups, Tidak ada kategori</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
    {{-- end categories section --}}
    {{-- product section --}}
    <section class="py-20 bg-gray-50 px-10" id="products">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-10 text-gray-600">Featured Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 justify-center">
                @forelse ($products as $product)
                    <div class=" bg-white p-6 rounded-lg shadow-lg text-center hover:scale-105 transition">
                        <a href="{{ route('front.product.details', $product->slug) }}"
                            class="hover:scale-105 transition">
                            <img src=" {{ Storage::url($product->photo) }} " alt="{{ $product->name }}"
                                class="w-full h-48 object-cover mb-4 rounded">
                            <h3 class="text-xl font-semibold mb-2">{{ $product->name }}</h3>
                            <span class="text-red-600 font-bold mb-2"> Rp {{ number_format($product->price) }}
                            </span>
                            <p class="text-gray-600 mb-4">Tersedia: {{ $product->stock }}</p>
                        </a>
                        <form action="{{ route('carts.add', $product->id) }}" method="POST">
                            @csrf
                            <button type="submit"
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
            <div class="flex justify-center mt-10">
                <a href="{{ route('front.product') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded transition mt-4">Selengkapnya</a>
            </div>
        </div>

    </section>
    {{-- end product section --}}

    {{-- testimonial section --}}
    <section class="py-20 bg-gray-100 px-10">
        <div class="container mx-auto">
            <h2 class="text-3xl font-bold text-center mb-10">What Our Customers Say</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Hendry - Jogja</h3>
                    <p class="text-gray-600 mb-4">Recomend banget nih tokonya! jangan lupa checkout ya :)</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Dinda - Jakarta</h3>
                    <p class="text-gray-600 mb-4">Belanja di sini nyaman banget! Koleksinya lengkap dan pengirimannya
                        cepat.</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Reza - Bandung</h3>
                    <p class="text-gray-600 mb-4">Prosesnya gampang, nggak ribet. Dan yang paling saya suka, ada ulasan
                        asli dari pembaca lain. Jadi bisa pilih buku dengan percaya diri.
                    </p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                    <h3 class="text-xl font-semibold mb-2">Andy - Surabaya</h3>
                    <p class="text-gray-600 mb-4">Koleksinya lengkap banget, dari novel sampai buku motivasi. Harganya
                        juga nggak bikin kantong jebol.</p>
                </div>
            </div>
        </div>
    </section>
    {{-- Cta section --}}
    <section class="py-20 px-10 bg-white">
        <div class="container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Bayangkan… di ujung jari kamu ada ribuan
                buku
                inspiratif,
                novel seru,
                panduan bisnis, buku anak, hingga koleksi langka. Semua bisa kamu dapatkan tanpa harus keluar rumah,
                dengan harga bersahabat dan pelayanan terbaik.</p>
            <div class="flex justify-center">
                <a href="#" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded">Shop
                    Now</a>
            </div>
        </div>
    </section>

    {{-- blog section --}}
    <section class="py-20 px-10 bg-gray-50">
        <div class="container mx-auto ">
            <h2 class="text-3xl font-bold text-center mb-10">Latest Article Posts</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach ($articles as $article)
                    <div class="bg-white p-6 rounded-lg shadow-lg hover:scale-105 transition">
                        <img src=" {{ Storage::url($article->featured_image) }}  " alt="{{ $article->title }}"
                            class="w-full h-48 object-cover mb-4 rounded">
                        <h3 class="text-xl font-semibold mb-2">{{ $article->title }}</h3>
                        <p class="text-gray-600 mb-4"> {{ Str::limit(strip_tags($article->content), 100, '...') }}</p>
                        <a href="{{ route('front.article.details', $article->slug) }}"
                            class="text-red-600 hover:underline">Read
                            More</a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-layout-front>
