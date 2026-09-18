<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detail Pesanan #') . $product_transaction->id }}
            </h2>
            <a href="{{ route('product_transactions.index') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">
                All Orders
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="item-card flex gap-y-3 flex-col md:flex-row justify-between md:items-center">
                    <div class="flex flex-col md:flex-row md:items-center gap-x-3">
                        <div>
                            <p class="text-base text-slate-500">Total Transaksi</p>
                            <h3 class="text-xl font-bold text-indigo-900">{{ rupiah($product_transaction->total_amount) }}</h3>
                        </div>
                    </div>
                    <div>
                        <p class="text-base text-slate-500">Tanggal</p>
                        <h3 class="text-xl font-bold text-indigo-900">{{ $product_transaction->created_at->idLong() }}</h3>
                    </div>
                    <span class="font-bold py-1 px-5 rounded-full w-fit {{ $product_transaction->statusBadgeColor() }}">
                        {{ $product_transaction->statusLabel() }}
                    </span>
                </div>

                @if (in_array($product_transaction->status, ['shipped', 'completed']) && $product_transaction->tracking_number)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-slate-500">Nomor Resi</p>
                        @php
                            $waMessage = "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda sudah dikirim. Nomor resi: {$product_transaction->tracking_number} ({$product_transaction->shipping_method}).";
                        @endphp
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-lg font-bold text-indigo-900">{{ $product_transaction->tracking_number }}</p>
                            <a href="{{ \App\Support\WaNotifier::url($product_transaction->phone_number, $waMessage) }}"
                                target="_blank" class="shrink-0 font-semibold text-sm bg-green-500 text-white py-1.5 px-3 rounded-full hover:bg-green-700">
                                <i class="fab fa-whatsapp mr-1"></i> Kirim Resi
                            </a>
                        </div>
                    </div>
                @endif

                @if ($product_transaction->status === 'rejected' && $product_transaction->rejection_note)
                    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-slate-500">Alasan Penolakan</p>
                        <p class="text-base font-bold text-red-800">{{ $product_transaction->rejection_note }}</p>
                    </div>
                @endif

                @if ($product_transaction->returns->isNotEmpty())
                    <div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-slate-500">Retur / Pengembalian</p>
                        <div class="mt-2 space-y-2">
                            @foreach ($product_transaction->returns->sortByDesc('id') as $returnRequest)
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-semibold rounded-full text-white px-3 py-1 {{ $returnRequest->statusBadgeColor() }}">
                                        {{ $returnRequest->statusLabel() }}
                                    </span>
                                    <p class="text-sm text-slate-700">#{{ $returnRequest->id }} · {{ $returnRequest->reason }}</p>
                                </div>
                                @if ($returnRequest->admin_note)
                                    <p class="text-xs text-slate-500">Catatan: {{ $returnRequest->admin_note }}</p>
                                @endif
                            @endforeach
                        </div>
                        @if ($product_transaction->status === \App\Models\ProductTransaction::STATUS_RETURNED)
                            <p class="text-xs text-slate-500 mt-2">Pesanan ini telah dikembalikan dan stok masuk kembali.</p>
                        @endif
                    </div>
                @endif

                <hr class="my-3">
                <h3 class="text-xl font-bold text-indigo-900">List of Item</h3>

                <div class="grid-cols-1 md:grid-cols-4 grid gap-y-10 md:gap-x-10">
                    <div class="flex flex-col gap-y-5 col-span-2">
                        @forelse ($product_transaction->transactionDetails as $list_product)
                            <div class="item-card flex flex-row justify-between items-center">
                                <div class="flex flex-row items-center gap-x-3">
                                    <img src="{{ Storage::url($list_product->product->photo) }}" alt=""
                                        class="w-[50px] h-[50px]">
                                    <div>
                                        <h3 class="text-xl font-bold text-indigo-900">{{ $list_product->product->name }}</h3>
                                        <p class="text-base text-slate-500">{{ rupiah($list_product->product->price) }}</p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-500">{{ $list_product->qty }} Pcs</p>
                            </div>
                        @empty
                            <p>Ups, transaksi terbaru belum tersedia!</p>
                        @endforelse

                        @php
                            $subTotal = $product_transaction->transactionDetails->sum(fn($d) => $d->price * $d->qty);
                            $taxPercent = $subTotal > 0 ? round(($product_transaction->tax_amount ?? 0) / $subTotal * 100, 1) : 0;
                            $insurancePercent = $subTotal > 0 ? round(($product_transaction->insurance_amount ?? 0) / $subTotal * 100, 1) : 0;
                        @endphp
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                            <h4 class="text-lg font-bold text-indigo-900">Rincian Biaya</h4>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Subtotal ({{ $product_transaction->transactionDetails->sum('qty') }} item)</span>
                                <span class="font-semibold text-indigo-900">{{ rupiah($subTotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Pajak ({{ number_format($taxPercent, 1) }}%)</span>
                                <span class="font-semibold text-indigo-900">{{ rupiah($product_transaction->tax_amount ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Asuransi ({{ number_format($insurancePercent, 1) }}%)</span>
                                <span class="font-semibold text-indigo-900">{{ rupiah($product_transaction->insurance_amount ?? 0) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-500">Pengiriman</span>
                                <span class="font-semibold text-indigo-900">{{ $product_transaction->shipping_cost ? rupiah($product_transaction->shipping_cost) : 'Gratis' }}</span>
                            </div>
                            <hr class="my-1">
                            <div class="flex justify-between text-base font-bold text-indigo-900">
                                <span>Total Dibayar</span>
                                <span>{{ rupiah($product_transaction->total_amount) }}</span>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-indigo-900">Detail Pembayaran</h3>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Metode Pembayaran</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->payment_method ?? '-' }}</h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Pengiriman</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->shipping_method ?? '-' }}
                                    <span class="text-base font-normal">({{
                                        $product_transaction->shipping_cost ? rupiah($product_transaction->shipping_cost) : 'Gratis'
                                    }})</span>
                                </h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Penerima</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->recipient_name ?? $product_transaction->user?->name }}</h3>
                                <p class="text-base text-slate-500">{{ $product_transaction->phone_number }}</p>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Alamat</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->address }}</h3>
                                <p class="text-base text-slate-500">
                                    {{ trim(implode(', ', array_filter([$product_transaction->district, $product_transaction->city, $product_transaction->province, $product_transaction->post_code]))) }}
                                </p>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Note</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->notes }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-y-5 col-span-2 items-center">
                        <h3 class="text-xl font-bold text-indigo-900">Proof of Payment</h3>
                        @if ($product_transaction->proof)
                            <img src="{{ Storage::url($product_transaction->proof) }}" alt=""
                                class="w-[300px] bg-white-500 h-[400px] object-contain">
                        @else
                            <div class="w-[300px] h-[200px] text-center text-gray-400 border-2 border-dashed border-gray-200 rounded-lg flex flex-col items-center justify-center gap-2">
                                <i class="fas fa-camera text-3xl"></i>
                                <p class="text-sm">Belum ada bukti pembayaran.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <hr class="my-3">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    @hasanyrole(['owner', 'admin'])
                        @if ($product_transaction->status === 'pending')
                            <form method="POST" action="{{ route('admin.orders.approve', $product_transaction) }}">
                                @csrf
                                <button type="submit"
                                    class="w-fit font-semibold bg-indigo-700 text-white py-2 px-4 rounded-full hover:bg-indigo-900">
                                    <i class="fas fa-check mr-1"></i> Approve & Kurangi Stok
                                </button>
                            </form>
                            <button type="button" x-data="" @click="$dispatch('open-modal', 'reject-order')"
                                class="w-fit font-semibold bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-800">
                                <i class="fas fa-times mr-1"></i> Tolak Pesanan
                            </button>
                        @elseif ($product_transaction->status === 'processing')
                            <button type="button" x-data="" @click="$dispatch('open-modal', 'ship-order')"
                                class="w-fit font-semibold bg-indigo-700 text-white py-2 px-4 rounded-full hover:bg-indigo-900">
                                <i class="fas fa-paper-plane mr-1"></i> Input Resi & Kirim
                            </button>
                        @elseif ($product_transaction->status === 'shipped')
                            <form method="POST" action="{{ route('admin.orders.complete', $product_transaction) }}">
                                @csrf
                                <button type="submit"
                                    class="w-fit font-semibold bg-green-600 text-white py-2 px-4 rounded-full hover:bg-green-800">
                                    <i class="fas fa-check-circle mr-1"></i> Tandai Selesai
                                </button>
                            </form>
                        @endif

                        @php
                            $cleanedPhone = \App\Support\WaNotifier::phoneToWa($product_transaction->phone_number);
                            $waPending = "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda sedang menunggu konfirmasi di Wigati Buku.";
                            $waApproved = "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda telah kami terima dan sedang diproses.";
                        @endphp
                        <a href="{{ \App\Support\WaNotifier::url($product_transaction->phone_number, in_array($product_transaction->status, ['pending']) ? $waPending : $waApproved) }}"
                            target="_blank"
                            class="w-fit font-semibold bg-green-500 text-white py-2 px-4 rounded-full hover:bg-green-700">
                            <i class="fab fa-whatsapp mr-1"></i> WhatsApp Customer
                        </a>
                    @endhasanyrole
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Reject --}}
    <x-modal name="reject-order" :show="false" focusable>
        <form method="POST" action="{{ route('admin.orders.reject', $product_transaction) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Tolak Pesanan #{{ $product_transaction->id }}</h2>
            <p class="mt-1 text-sm text-gray-600">Alasan penolakan akan dikirim ke pembeli.</p>
            <div class="mt-6">
                <x-input-label for="rejection_note" value="Alasan" />
                <textarea name="rejection_note" id="rejection_note" rows="5" required
                    class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm w-full resize-y"
                    placeholder="Tuliskan alasan penolakan secara lengkap agar pembeli mengerti..."></textarea>
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3 bg-red-600">{{ __('Tolak Pesanan') }}</x-primary-button>
            </div>
        </form>
    </x-modal>

    {{-- Modal: Ship --}}
    <x-modal name="ship-order" :show="false" focusable>
        <form method="POST" action="{{ route('admin.orders.ship', $product_transaction) }}" class="p-6">
            @csrf
            <h2 class="text-lg font-medium text-gray-900">Kirim Pesanan #{{ $product_transaction->id }}</h2>
            <p class="mt-1 text-sm text-gray-600">Kurir: {{ $product_transaction->shipping_method ?? '-' }}. Nomor resi wajib diisi.</p>
            <div class="mt-6">
                <x-input-label for="tracking_number" value="Nomor Resi" />
                <x-text-input id="tracking_number" name="tracking_number" type="text" class="mt-1 block w-full" required />
            </div>
            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">Batal</x-secondary-button>
                <x-primary-button class="ms-3">{{ __('Kirim Pesanan') }}</x-primary-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>