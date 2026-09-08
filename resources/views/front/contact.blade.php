<x-layout-front title="Contact - Wigati Buku">
    {{-- herosection --}}
    <section class="py-20 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Contact</h4>
            <p class="text-lg mb-8 text-gray-600">Kami siap membantu Anda! Isi formulir atau hubungi kami melalui
                informasi di bawah ini.</p>
        </div>
    </section>
    <div class="bg-gray-50 text-gray-800">
        {{-- Konten Utama --}}
        <section class="container mx-auto px-4 py-16 grid md:grid-cols-2 gap-12">
            {{-- Form Kontak --}}
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold mb-6">Kirim Pesan</h2>
                <form id="contactForm" onsubmit="event.preventDefault(); const n = document.getElementById('name').value; const m = document.getElementById('message').value; window.open('https://wa.me/6281234567890?text=' + encodeURIComponent('Halo, nama saya ' + n + '. Pesan: ' + m), '_blank');" class="space-y-6">
                    <div>
                        <label for="name" class="block font-semibold mb-2">Nama</label>
                        <input type="text" id="name" name="name"
                            class="block w-full py-3 pl-3 pr-10 border rounded-lg border-gray-300 shadow-sm focus:ring-red-500 focus:border-red-500"
                            required>
                    </div>
                    <div>
                        <label for="email" class="block font-semibold mb-2">Email</label>
                        <input type="email" id="email" name="email"
                            class="block w-full py-3 pl-3 pr-10 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500"
                            required>
                    </div>
                    <div>
                        <label for="message" class="block font-semibold mb-2">Pesan</label>
                        <textarea id="message" name="message" rows="5"
                            class="block w-full py-3 pl-3 pr-10 border border-gray-300 rounded-lg shadow-sm focus:ring-red-500 focus:border-red-500"
                            required></textarea>
                    </div>
                    <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-lg transition">
                        <i class="fas fa-paper-plane mr-2"></i>Kirim Pesan
                    </button>
                </form>
            </div>

            {{-- Info Kontak --}}
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold mb-4">Informasi Kontak</h2>
                    <p class="mb-4 text-gray-600">Silakan hubungi kami melalui alamat atau telepon di bawah ini.</p>
                    <ul class="space-y-4 text-gray-700">
                        <li><i class="fas fa-map-marker-alt text-red-600 mr-2"></i> Jl. Contoh No. 123, Jakarta</li>
                        <li><i class="fas fa-phone-alt text-red-600 mr-2"></i> +62 812-3456-7890</li>
                        <li><i class="fas fa-envelope text-red-600 mr-2"></i> support@contoh.com</li>
                    </ul>
                </div>

                {{-- Google Maps --}}
                <div class="rounded-lg overflow-hidden shadow-lg">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.860233473139!2d106.82715381522968!3d-6.175387995530814!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f3e8e1b9bcb5%3A0x3f4e33b3d1b4e23d!2sMonas!5e0!3m2!1sid!2sid!4v1695312304918!5m2!1sid!2sid"
                        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy">
                    </iframe>
                </div>
            </div>
        </section>

    </div>
    {{-- Section CTA --}}
    <section>
        <div class="py-20 px-10 bg-white container mx-auto">
            <p class="text-2xl font-bold text-center text-gray-600 mb-6 px-6">Ingin tahu lebih banyak
                tentang Wigati
                Buku?
            </p>
            <div class="text-center">
                <a href="{{ route('front.about') }}"
                    class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded transition">
                    About Us
                </a>
            </div>
        </div>
    </section>
</x-layout-front>
