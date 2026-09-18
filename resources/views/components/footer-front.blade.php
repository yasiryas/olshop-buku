<footer class="bg-gray-800 text-white">
    <!-- CTA Section -->
    <div class="bg-red-600 py-10">
        <div class="container mx-auto px-4 text-center">
            <h3 class="text-2xl md:text-3xl font-bold mb-3">Siap Temukan Buku Favoritmu?</h3>
            <p class="text-red-100 mb-6 max-w-2xl mx-auto">
                Ribuan judul buku menunggu kamu. Belanja mudah, cepat, dan aman hanya di Wigati Buku.
            </p>
            <a href="{{ route('front.product') }}"
                class="inline-flex items-center gap-2 bg-white text-red-600 font-semibold py-3 px-8 rounded-full hover:bg-red-50 transition">
                <i class="fa-solid fa-bag-shopping"></i> Mulai Belanja
            </a>
        </div>
    </div>

    <!-- Footer Links -->
    <div class="py-10 px-4">
        <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <a href="{{ route('front.index') }}" class="inline-block mb-4">
                    <img src="{{ asset('/assets/logo/logo-wigati.webp') }}" alt="Wigati Buku" class="w-[150px]">
                </a>
                <p class="text-gray-300 text-sm leading-relaxed max-w-xs">
                    Toko buku online terpercaya menyediakan ribuan judul dari berbagai genre.
                    Belanja nyaman dengan pengiriman cepat ke seluruh Indonesia.
                </p>
            </div>

            <div>
                <h4 class="font-semibold mb-4">Navigasi</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    <li><a href="{{ route('front.index') }}" class="hover:text-white transition">Beranda</a></li>
                    <li><a href="{{ route('front.product') }}" class="hover:text-white transition">Produk</a></li>
                    <li><a href="{{ route('front.blog') }}" class="hover:text-white transition">Blog</a></li>
                    <li><a href="{{ route('front.about') }}" class="hover:text-white transition">Tentang Kami</a></li>
                    <li><a href="{{ route('front.contact') }}" class="hover:text-white transition">Kontak</a></li>
                </ul>
            </div>

            <div>
                <h4 class="font-semibold mb-4">Akun</h4>
                <ul class="space-y-2 text-sm text-gray-300">
                    @guest
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Masuk</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Daftar</a></li>
                    @endguest
                    @auth
                        @role('buyer')
                            <li><a href="{{ route('carts.index') }}" class="hover:text-white transition">Keranjang</a></li>
                            <li><a href="{{ route('product_transactions.index') }}" class="hover:text-white transition">Pesanan Saya</a></li>
                        @endrole
                        @role('admin|penulis|owner')
                            <li><a href="{{ route('dashboard') }}" class="hover:text-white transition">Dashboard</a></li>
                        @endrole
                        <li><form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="hover:text-white transition text-left">Keluar</button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom Bar -->
    <div class="bg-gray-900 py-4 border-t border-gray-700">
        <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-400 text-center md:text-left">
                &copy; {{ date('Y') }} {{ config('app.name', 'Wigati Buku') }}. Hak cipta dilindungi.
            </p>
            <div class="flex items-center gap-6 text-sm text-gray-400">
                <a href="#" class="hover:text-white transition" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="hover:text-white transition" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="hover:text-white transition" aria-label="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="hover:text-white transition" aria-label="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
