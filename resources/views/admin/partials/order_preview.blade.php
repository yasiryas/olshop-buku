<div class="space-y-5 text-sm">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Pesanan #{{ $product_transaction->id }}</h3>
            <p class="text-gray-500">{{ $product_transaction->created_at->idDateTime() }}</p>
        </div>
        <span class="font-semibold py-1 px-4 rounded-full {{ $product_transaction->statusBadgeColor() }}">
            {{ $product_transaction->statusLabel() }}
        </span>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-gray-500">Pembeli</p>
            <p class="font-bold text-gray-900">{{ $product_transaction->user->name ?? '-' }}</p>
            <p class="text-gray-600">{{ $product_transaction->phone_number }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-gray-500">Total</p>
            <p class="font-bold text-gray-900">Rp {{ number_format($product_transaction->total_amount) }}</p>
            <p class="text-gray-600">{{ $product_transaction->payment_method ?? '-' }}</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-gray-500">Pengiriman</p>
            <p class="font-bold text-gray-900">{{ $product_transaction->shipping_method ?? '-' }}</p>
            <p class="text-gray-600">
                {{ $product_transaction->shipping_cost ? 'Rp ' . number_format($product_transaction->shipping_cost) : 'Gratis' }}
                @if ($product_transaction->tracking_number)
                    · {{ $product_transaction->tracking_number }}
                @endif
            </p>
        </div>
    </div>

    @if ($product_transaction->status === 'rejected' && $product_transaction->rejection_note)
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
            <p class="text-gray-500">Alasan Penolakan</p>
            <p class="font-bold text-red-800">{{ $product_transaction->rejection_note }}</p>
        </div>
    @endif

    @if ($product_transaction->returns->isNotEmpty())
        <div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
            <p class="text-gray-500">Retur / Pengembalian</p>
            <div class="mt-2 space-y-1">
                @foreach ($product_transaction->returns->sortByDesc('id') as $returnRequest)
                    <p class="text-gray-700">
                        #{{ $returnRequest->id }} · {{ $returnRequest->reason }}
                        @if ($returnRequest->admin_note)
                            — Catatan: {{ $returnRequest->admin_note }}
                        @endif
                    </p>
                @endforeach
            </div>
        </div>
    @endif

    <div>
        <h4 class="font-bold text-gray-900">List Item</h4>
        <table class="w-full mt-2 text-sm">
            <tbody class="divide-y divide-gray-100">
                @foreach ($product_transaction->transactionDetails as $detail)
                    <tr>
                        <td class="py-2">
                            <p class="font-medium text-gray-900">{{ $detail->product->name ?? '-' }}</p>
                        </td>
                        <td class="py-2 text-right text-gray-500">{{ $detail->qty }} Pcs</td>
                        <td class="py-2 text-right text-gray-900">Rp {{ number_format($detail->product->price ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-gray-50 rounded-lg p-3">
            <p class="text-gray-500">Alamat</p>
            <p class="font-bold text-gray-900">{{ $product_transaction->recipient_name ?? $product_transaction->user?->name }} · {{ $product_transaction->phone_number }}</p>
            <p class="text-gray-900">{{ $product_transaction->address }}</p>
            <p class="text-gray-600">{{ trim(implode(', ', array_filter([$product_transaction->district, $product_transaction->city, $product_transaction->province, $product_transaction->post_code]))) }}</p>
            @if ($product_transaction->notes)
                <p class="text-gray-600 mt-1">Catatan: {{ $product_transaction->notes }}</p>
            @endif
        </div>
        @if ($product_transaction->proof)
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-gray-500">Bukti Pembayaran</p>
                <a href="{{ Storage::url($product_transaction->proof) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">
                    <i class="fas fa-image mr-1"></i> Lihat Bukti
                </a>
            </div>
        @endif
    </div>

    <div class="flex flex-wrap gap-3 items-center border-t border-gray-100 pt-4">
        @if ($product_transaction->status === 'pending')
            <form method="POST" action="{{ route('admin.orders.approve', $product_transaction) }}">
                @csrf
                <button type="submit"
                    class="font-semibold bg-indigo-600 text-white py-2 px-4 rounded-full hover:bg-indigo-700">
                    <i class="fas fa-check mr-1"></i> Approve & Kurangi Stok
                </button>
            </form>
            <div x-data="{ rejectOpen: false }" class="w-full sm:w-auto">
                <button type="button" @click="rejectOpen = !rejectOpen"
                    class="font-semibold bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700">
                    <i class="fas fa-times mr-1"></i> Tolak Pesanan
                </button>
                <form method="POST" action="{{ route('admin.orders.reject', $product_transaction) }}"
                    x-show="rejectOpen" x-cloak
                    class="mt-4 -mx-1 bg-red-50 border border-red-200 rounded-lg p-4 w-full sm:w-auto sm:min-w-[480px]">
                    @csrf
                    <label for="rejection_note" class="block text-gray-700 font-medium mb-1">Alasan penolakan</label>
                    <textarea name="rejection_note" rows="4" required autofocus
                        class="border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm w-full text-sm resize-y"
                        placeholder="Tuliskan alasan penolakan secara lengkap agar pembeli mengerti..."></textarea>
                    <div class="mt-3 flex justify-end gap-2">
                        <button type="button" @click="rejectOpen = false"
                            class="font-semibold bg-white text-gray-700 border border-gray-300 py-2 px-4 rounded-full hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit"
                            class="font-semibold bg-red-600 text-white py-2 px-4 rounded-full hover:bg-red-700">
                            Tolak Pesanan
                        </button>
                    </div>
                </form>
            </div>
        @elseif ($product_transaction->status === 'processing')
            <div x-data="{ shipOpen: false }">
                <button type="button" @click="shipOpen = !shipOpen"
                    class="font-semibold bg-indigo-600 text-white py-2 px-4 rounded-full hover:bg-indigo-700">
                    <i class="fas fa-paper-plane mr-1"></i> Input Resi & Kirim
                </button>
                <form method="POST" action="{{ route('admin.orders.ship', $product_transaction) }}"
                    x-show="shipOpen" x-cloak class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                    @csrf
                    <p class="text-gray-600 mb-1">Kurir: {{ $product_transaction->shipping_method ?? '-' }}. Nomor resi wajib diisi.</p>
                    <input type="text" name="tracking_number" placeholder="Nomor resi..." required
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm">
                    <div class="mt-2 text-right">
                        <button type="submit" class="font-semibold bg-indigo-600 text-white py-2 px-4 rounded-full hover:bg-indigo-700">
                            Kirim Pesanan
                        </button>
                    </div>
                </form>
            </div>
        @elseif ($product_transaction->status === 'shipped')
            <form method="POST" action="{{ route('admin.orders.complete', $product_transaction) }}">
                @csrf
                <button type="submit"
                    class="font-semibold bg-green-600 text-white py-2 px-4 rounded-full hover:bg-green-700">
                    <i class="fas fa-check-circle mr-1"></i> Tandai Selesai
                </button>
            </form>
        @endif

        @php
            $cleanedPhone = \App\Support\WaNotifier::phoneToWa($product_transaction->phone_number);
            $waPending = "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda sedang menunggu konfirmasi di Wigati Buku.";
            $waApproved = "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda telah kami terima dan sedang diproses.";
            $waResi = $product_transaction->tracking_number
                ? "Halo {$product_transaction->user->name}, pesanan #{$product_transaction->id} Anda sudah dikirim. Nomor resi: {$product_transaction->tracking_number} ({$product_transaction->shipping_method})."
                : $waApproved;
        @endphp
        <a href="{{ \App\Support\WaNotifier::url($product_transaction->phone_number, in_array($product_transaction->status, ['pending']) ? $waPending : $waResi) }}"
            target="_blank"
            class="font-semibold bg-green-500 text-white py-2 px-4 rounded-full hover:bg-green-700">
            <i class="fab fa-whatsapp mr-1"></i> WhatsApp Customer
        </a>
    </div>
</div>