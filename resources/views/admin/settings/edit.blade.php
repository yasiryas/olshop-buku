<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pengaturan Toko') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

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

                    {{-- Shipping --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-800">Metode Pengiriman / Ongkir</h3>
                                <button type="button" @click="addShipping()"
                                    class="text-sm font-bold text-indigo-700 hover:text-indigo-900">+ Tambah Kurir</button>
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
                                                :name="`shipping_methods[${i}][cost]`" placeholder="Ongkir"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-3">
                                            <input type="text" x-model="row.eta"
                                                :name="`shipping_methods[${i}][eta]`" placeholder="Estimasi"
                                                class="w-full border rounded-lg px-3 py-2 text-sm">
                                        </div>
                                        <div class="md:col-span-2 flex items-center justify-end">
                                            <button type="button" @click="removeShipping(i)"
                                                class="text-sm text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
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
                                    class="text-sm font-bold text-indigo-700 hover:text-indigo-900">+ Tambah Metode</button>
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
                                                class="text-sm text-red-600 hover:text-red-800"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-700 text-white font-bold py-3 rounded-xl hover:bg-indigo-900">
                        Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script type="application/json" id="settings-data">{!! json_encode([
        'shipping' => $settings['shipping_methods'] ?? [],
        'payment' => $settings['payment_methods'] ?? [],
        'wa' => $settings['wa_contact'] ?? '',
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>

    <script>
        document.addEventListener('alpine:init', () => {
            const json = JSON.parse(document.getElementById('settings-data').textContent);
            Alpine.data('storeSettings', () => ({
                wa: json.wa,
                shipping: (json.shipping || []).map(s => ({ ...s })),
                payment: (json.payment || []).map(p => ({ ...p })),

                addShipping() {
                    this.shipping.push({ code: 'courier-' + (this.shipping.length + 1), courier: '', cost: 0, eta: '' });
                },
                removeShipping(i) {
                    this.shipping.splice(i, 1);
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