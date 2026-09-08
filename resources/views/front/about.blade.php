<x-layout-front title="Blog - Wigati Buku">
    {{-- herosection --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">About Us</h4>
            <p class="text-lg mb-8 text-gray-600">Tentang Wigati Buku</p>
        </div>
    </section>
    {{-- Content section --}}
    {{-- Tentang Kami --}}
    <section class=" mx-auto px-4 py-16 grid md:grid-cols-2 gap-10 items-center bg-gray-100 w-full">
        <div>
            <img src="{{ asset('assets/logo/logo-wigati.webp') }}" alt="Tentang Kami" class="w-[400px] mx-auto">
        </div>
        <div>
            <h2 class="text-3xl font-bold mb-4">Siapa Kami?</h2>
            <p class="text-gray-600 mb-6">
                Kami adalah perusahaan yang berdedikasi untuk memberikan produk dan layanan terbaik
                kepada pelanggan. Dengan pengalaman bertahun-tahun, kami berkomitmen untuk
                memberikan inovasi, kualitas, dan kepuasan pelanggan.
            </p>
            <a href="{{ route('front.contact') }}"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 mt-4 rounded font-semibold">Hubungi Kami</a>
        </div>
    </section>

    {{-- Visi dan Misi --}}
    <section class="bg-gray-50 py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-10">Visi & Misi</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white p-8 rounded-lg shadow hover:shadow-xl transition">
                    <h3 class="text-2xl font-semibold mb-4">Visi</h3>
                    <p class="text-gray-600">
                        Menjadi perusahaan terdepan dalam memberikan solusi inovatif yang bermanfaat
                        bagi masyarakat luas.
                    </p>
                </div>
                <div class="bg-white p-8 rounded-lg shadow hover:shadow-xl transition">
                    <h3 class="text-2xl font-semibold mb-4">Misi</h3>
                    <p class="text-gray-600">
                        Memberikan produk berkualitas tinggi dengan pelayanan terbaik,
                        serta menciptakan nilai tambah bagi pelanggan dan mitra.
                    </p>
                </div>
            </div>
        </div>
    </section>


    {{-- Section CTA --}}
    <section>
        <div class="py-20 px-10 bg-white container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Dapatkan informasi lebih lanjut atau ingin
                menghubungi kami?
            </p>
            <div class="text-center">
                <a href="{{ route('front.contact') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded transition">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>
</x-layout-front>
