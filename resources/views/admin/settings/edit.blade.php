<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Toko') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div x-data="storeSettings">
                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- WA Contact --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Kontak WhatsApp</h3>
                            <p class="text-xs text-gray-500 mb-2">Nomor untuk tautan "hubungi toko" (format 62...).</p>
                            <input type="text" x-model="wa" name="wa_contact"
                                class="w-full border rounded-lg px-4 py-2">
                        </div>
                    </div>

                    {{-- AgenWebsite --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ongkir Real-time (AgenWebsite)</h3>
                            <p class="text-xs text-gray-500 mb-4">
                                Daftar 501 kota seluruh Indonesia sudah tersedia otomatis (seeder) — pilih Kota Asal lalu Simpan.
                                Ongkir dihitung dari AgenWebsite Rate API (J&T, Lion Parcel, SAP, SPX, J&T Cargo) bila API key terisi
                                (gratis: 150 request/hari dari agenwebsite.com); kosongkan untuk tarif zona manual.
                            </p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600">API Key</label>
                                    <input type="password" x-model="agenwebApiKey" name="agenweb_api_key"
                                        autocomplete="new-password"
                                        placeholder="API key dari agenwebsite.com (awk_live_...)"
                                        class="w-full border rounded-lg px-4 py-2 text-sm">
                                    <p class="text-xs text-gray-400 mt-1">Prioritas: .env <code>WIGATI_AGENWEB_API_KEY</code>.
                                        Kosongkan bila sudah diatur di .env.</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Kota Asal (Toko)</label>
                                    <select name="agenweb_origin_city_id" x-select2
                                        class="w-full border rounded-lg px-4 py-2 text-sm">
                                        <option value="">-- pilih kota --</option>
                                        @foreach ($agenWebCities as $city)
                                            <option value="{{ $city['city_id'] }}"
                                                @selected((string) ($settings['agenweb_origin_city_id'] ?? '') === (string) $city['city_id'])>
                                                {{ $city['city_name'] }} - {{ $city['province'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Kode Pos Toko (Asal)</label>
                                    <input type="text" x-model="agenwebOriginPostalCode" name="agenweb_origin_postal_code"
                                        placeholder="Kode pos kecamatan toko, contoh: 55651 (Wates, Kulon Progo)"
                                        class="w-full border rounded-lg px-4 py-2 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Low stock --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Peringatan Stok Menipis</h3>
                            <p class="text-xs text-gray-500 mb-2">Produk dengan stok ≤ ambang ini ditandai "Menipis" di dashboard & laporan.</p>
                            <input type="number" x-model="lowStockThreshold" name="low_stock_threshold" min="0"
                                class="w-32 border rounded-lg px-4 py-2">
                        </div>
                    </div>

                    {{-- Tax & Insurance --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pajak & Asuransi</h3>
                            <p class="text-xs text-gray-500 mb-4">Persentase default untuk perhitungan otomatis saat checkout. Bisa diubah manual saat approve pesanan.</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Pajak (%)</label>
                                    <input type="number" x-model="taxPercent" name="tax_percent" min="0" step="0.1" max="100"
                                        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="11">
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-600">Asuransi (%)</label>
                                    <input type="number" x-model="insurancePercent" name="insurance_percent" min="0" step="0.1" max="100"
                                        class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="2.3">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Shipping --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Metode Pengiriman / Ongkir</h3>
                                <button type="button" @click="addShipping()"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-full text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150">+ Tambah Kurir</button>
                            </div>
                            <div class="space-y-4">
                                <template x-for="(row, i) in shipping" :key="i">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-gray-50 rounded-lg p-4">
                                        <input type="hidden" :name="`shipping_methods[${i}][code]`" x-model="row.code">
                                        <div class="md:col-span-4">
                                            <input type="text" x-model="row.courier"
                                                :name="`shipping_methods[${i}][courier]`" placeholder="Nama kurir"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="number" x-model="row.cost" min="0"
                                                :name="`shipping_methods[${i}][cost]`" placeholder="Ongkir (fallback)"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="row.eta"
                                                :name="`shipping_methods[${i}][eta]`" placeholder="Estimasi"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-2 flex items-center justify-end">
                                            <button type="button" @click="removeShipping(i)"
    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white bg-red-600 hover:bg-red-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150"><i class="fas fa-trash text-xs"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Zones --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Zona / Tarif per Kota</h3>
                                <button type="button" @click="addZone()"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-full text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150">+ Tambah Zona</button>
                            </div>
                            <p class="text-xs text-gray-500 mb-4">Biaya kurir per kota. Kota tanpa tarif khusus memakai nilai fallback kurir.</p>
                            <div class="space-y-4">
                                <template x-for="(zone, i) in zones" :key="i">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between gap-3 mb-3">
                                            <input type="text" x-model="zone.city"
                                                :name="`shipping_zones[${i}][city]`" placeholder="Nama kota"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <button type="button" @click="removeZone(i)"
    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white bg-red-600 hover:bg-red-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150 shrink-0"><i class="fas fa-trash text-xs"></i></button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3" x-show="shipping.length">
                                            <template x-for="(curr, ci) in shipping" :key="ci">
                                                <div>
                                                    <label class="text-xs text-gray-500" x-text="curr.courier || 'Kurir'"></label>
                                                    <input type="number" min="0" placeholder="0"
                                                        :name="`shipping_zones[${i}][costs][${curr.code}]`"
                                                        x-model="zone.costs[curr.code]"
                                                        class="w-full border rounded-lg px-3 py-2 text-sm">
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Metode Pembayaran</h3>
                                <button type="button" @click="addPayment()"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold px-4 py-2 rounded-full text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150">+ Tambah Metode</button>
                            </div>
                            <div class="space-y-4">
                                <template x-for="(row, i) in payment" :key="i">
                                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3 bg-gray-50 rounded-lg p-4 items-center">
                                        <input type="hidden" :name="`payment_methods[${i}][code]`" x-model="row.code">
                                        <input type="hidden" :name="`payment_methods[${i}][active]`" :value="row.active ? 1 : 0">
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="row.name"
                                                :name="`payment_methods[${i}][name]`" placeholder="Nama metode"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="row.acc_number"
                                                :name="`payment_methods[${i}][acc_number]`" placeholder="No. rekening"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="row.acc_name"
                                                :name="`payment_methods[${i}][acc_name]`" placeholder="Atas nama"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-2 flex items-center gap-4">
                                            <label class="flex items-center gap-1 text-sm text-gray-600">
                                                <input type="checkbox" x-model="row.active" class="rounded"> Aktif
                                            </label>
                                        </div>
                                        <div class="md:col-span-1 flex items-center justify-end">
                                            <button type="button" @click="removePayment(i)"
    class="inline-flex items-center justify-center w-8 h-8 rounded-full text-white bg-red-600 hover:bg-red-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition duration-150"><i class="fas fa-trash text-xs"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-700 text-white font-semibold py-2 rounded-full hover:bg-indigo-900">
                        Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="settings-data">{!! json_encode([
        'shipping' => $settings['shipping_methods'] ?? [],
        'zones' => $settings['shipping_zones'] ?? [],
        'payment' => $settings['payment_methods'] ?? [],
        'wa' => $settings['wa_contact'] ?? '',
        'agenweb_origin_postal_code' => $settings['agenweb_origin_postal_code'] ?? '',
        'low_stock_threshold' => $settings['low_stock_threshold'] ?? 5,
        'tax_percent' => $settings['tax_percent'] ?? 11,
        'insurance_percent' => $settings['insurance_percent'] ?? 2.3,
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script>
        document.addEventListener('alpine:init', () => {
            const json = JSON.parse(document.getElementById('settings-data').textContent);
            Alpine.data('storeSettings', () => ({
                wa: json.wa,
                agenwebApiKey: '',
                agenwebOriginPostalCode: json.agenweb_origin_postal_code ?? '',
                lowStockThreshold: json.low_stock_threshold,
                taxPercent: json.tax_percent ?? 11,
                insurancePercent: json.insurance_percent ?? 2.3,
                shipping: (json.shipping || []).map(s => ({ ...s })),
                zones: (json.zones || []).map(z => ({ city: z.city, costs: { ...(z.costs || {}) } })),
                payment: (json.payment || []).map(p => ({ ...p })),

                addShipping() {
                    this.shipping.push({ code: 'courier-' + (this.shipping.length + 1), courier: '', cost: 0, eta: '' });
                },
                removeShipping(i) {
                    this.shipping.splice(i, 1);
                },
                addZone() {
                    this.zones.push({ city: '', costs: {} });
                },
                removeZone(i) {
                    this.zones.splice(i, 1);
                },
                addPayment() {
                    this.payment.push({ code: 'method-' + (this.payment.length + 1), name: '', acc_number: '', acc_name: '', active: true });
                },
                removePayment(i) {
                    this.payment.splice(i, 1);
                }
            }));
        });
    </script>
</x-app-layout>