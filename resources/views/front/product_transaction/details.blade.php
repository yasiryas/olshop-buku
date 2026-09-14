<x-layout-front title="Detail Transaksi - Wigati Buku" robots="noindex, nofollow">
    <section class="py-16 px-10 space-x-6 container mx-auto flex items-center justify-between ">
        <div class="container mx-auto w-3/6 text-center">
            <h4 class="text-4xl font-bold mb-4 text-gray-600">Detail Pesanan #{{ $product_transaction->id }}</h4>
            <p class="text-lg mb-8 text-gray-600">Pantau status pesanan Anda di sini.</p>
            <a href="{{ route('product_transactions.index') }}"
                class="inline-flex items-center gap-2 font-bold text-indigo-600 hover:text-indigo-800 transition">
                <i class="fas fa-arrow-left"></i> Kembali ke Status Pembelian
            </a>
        </div>
    </section>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-grey-100 flex flex-col gap-y-5 p-10 overflow-hidden shadow-lg sm:rounded-lg border">

                {{-- Header: total / date / status --}}
                <div class="item-card flex gap-y-3 flex-col md:flex-row justify-between md:items-center">
                    <div>
                        <p class="text-base text-slate-500">Total Transaksi</p>
                        <h3 class="text-xl font-bold text-indigo-900">Rp {{ number_format($product_transaction->total_amount) }}</h3>
                    </div>
                    <div>
                        <p class="text-base text-slate-500">Tanggal</p>
                        <h3 class="text-xl font-bold text-indigo-900">{{ $product_transaction->created_at->idLong() }}</h3>
                    </div>
                    <span class="font-bold py-1 px-5 rounded-full w-fit {{ $product_transaction->statusBadgeColor() }}">
                        {{ $product_transaction->statusLabel() }}
                    </span>
                </div>

                {{-- Tracking progress --}}
                @if (in_array($product_transaction->status, ['processing', 'shipped', 'completed']))
                    @php
                        $flow = [
                            'processing' => 'Diproses',
                            'shipped' => 'Dikirim',
                            'completed' => 'Selesai',
                        ];
                        $flowKeys = array_keys($flow);
                        $currentIndex = array_search($product_transaction->status, $flowKeys);
                    @endphp
                    <div class="flex items-center gap-2">
                        @foreach ($flow as $i => $label)
                            @php
                                $stepIndex = array_search($i, $flowKeys);
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <div
                                    class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm {{ $stepIndex < $currentIndex ? 'bg-green-500' : ($stepIndex === $currentIndex ? 'bg-indigo-600' : 'bg-gray-200') }}">
                                    <i class="fas {{ $stepIndex < $currentIndex ? 'fa-check' : 'fa-circle' }}"></i>
                                </div>
                                <span class="text-xs text-gray-600">{{ $label }}</span>
                            </div>
                            @if (!$loop->last)
                                <div class="flex-1 h-0.5 {{ $stepIndex < $currentIndex ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                            @endif
                        @endforeach
                    </div>
                @elseif (in_array($product_transaction->status, ['rejected', 'cancelled']))
                    <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3">
                        <p class="text-base font-bold text-grey-800">
                            {{ $product_transaction->status === 'rejected' ? 'Pesanan Ditolak' : 'Pesanan Dibatalkan' }}
                        </p>
                        @if ($product_transaction->status === 'rejected' && $product_transaction->rejection_note)
                            <p class="text-sm text-red-700 mt-1">Alasan: {{ $product_transaction->rejection_note }}</p>
                        @endif
                    </div>
                @elseif ($product_transaction->status === \App\Models\ProductTransaction::STATUS_RETURNED)
                    <div class="bg-purple-50 border border-purple-200 rounded-lg px-4 py-3">
                        <p class="text-base font-bold text-purple-800">Pesanan Dikembalikan</p>
                        @if ($product_transaction->returns->first()?->admin_note)
                            <p class="text-sm text-purple-700 mt-1">Catatan: {{ $product_transaction->returns->first()->admin_note }}</p>
                        @endif
                    </div>
                @endif

                {{-- Resi --}}
                @if (in_array($product_transaction->status, ['shipped', 'completed']) && $product_transaction->tracking_number)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3">
                        <p class="text-sm text-slate-500">Nomor Resi ({{ $product_transaction->shipping_method }})</p>
                        <p class="text-2xl font-bold text-indigo-900">{{ $product_transaction->tracking_number }}</p>
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
                                        <p class="text-base text-slate-500">Rp {{ number_format($list_product->product->price) }}</p>
                                    </div>
                                </div>
                                <p class="text-base text-slate-500">{{ $list_product->qty }} Pcs</p>
                            </div>
                        @empty
                            <p>Ups, transaksi terbaru belum tersedia!</p>
                        @endforelse

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
                                        $product_transaction->shipping_cost ? 'Rp ' . number_format($product_transaction->shipping_cost) : 'Gratis'
                                    }})</span>
                                </h3>
                            </div>
                        </div>
                        <div class="item-card flex flex-row justify-between items-center">
                            <div>
                                <p class="text-base text-slate-500">Penerima</p>
                                <h3 class="text-lg font-bold text-indigo-900">{{ $product_transaction->recipient_name ?? $product_transaction->user->name }}</h3>
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
                                <p class="text-base text-slate-500">Catatan</p>
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
                                <p class="text-xs max-w-[220px]">Pesanan tidak akan diproses sampai bukti transfer diunggah.</p>
                            </div>
                        @endif

                        @if ($product_transaction->status === \App\Models\ProductTransaction::STATUS_PENDING)
                            @php
                                $account = collect(\App\Support\StoreSettings::paymentMethods())
                                    ->firstWhere('name', $product_transaction->payment_method);
                            @endphp
                            @if ($account && $account['acc_number'])
                                <div class="w-full max-w-[300px] bg-slate-50 border border-slate-200 rounded-lg px-4 py-3">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Transfer ke</p>
                                    <p class="text-sm font-bold text-indigo-900 mt-1">{{ $account['name'] }}</p>
                                    <p class="text-base font-bold text-indigo-900">{{ $account['acc_number'] }}</p>
                                    <p class="text-sm text-slate-500">a.n. {{ $account['acc_name'] }}</p>
                                </div>
                            @endif
                            <form action="{{ route('product_transactions.proof', $product_transaction->id) }}" method="POST"
                                enctype="multipart/form-data" class="w-full max-w-[300px]">
                                @csrf
                                @if ($product_transaction->proof)
                                    <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 text-center">
                                        Bukti sudah diunggah. Hubungi admin jika belum Dikonfirmasi.
                                    </p>
                                @else
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Upload Bukti Pembayaran</label>
                                    <input type="file" name="proof" accept="image/png,image/jpeg" required
                                        class="block w-full text-sm text-gray-600 border rounded-lg px-3 py-2 mb-2 file:mr-3 file:rounded-full file:border-0 file:bg-indigo-600 file:text-white file:py-1.5 file:px-4">
                                    <button type="submit"
                                        class="w-full font-bold bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-6 rounded-full transition text-sm">
                                        <i class="fas fa-upload mr-2"></i>Unggah Bukti
                                    </button>
                                @endif
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Retur --}}
                @if ($product_transaction->status === \App\Models\ProductTransaction::STATUS_COMPLETED)
                    @php $returnRequest = $product_transaction->returns->first(); @endphp
                    @if ($returnRequest)
                        <div class="bg-orange-50 border border-orange-200 rounded-lg px-4 py-3">
                            <p class="text-base font-bold text-gray-800">Pengajuan Retur</p>
                            <p class="text-sm text-gray-600 mt-1">
                                Status:
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full text-white {{ $returnRequest->statusBadgeColor() }}">
                                    {{ $returnRequest->statusLabel() }}
                                </span>
                                · Alasan: {{ $returnRequest->reason }}
                            </p>
                            @if ($returnRequest->description)
                                <p class="text-sm text-gray-500 mt-1">{{ $returnRequest->description }}</p>
                            @endif
                            @if ($returnRequest->admin_note)
                                <p class="text-sm text-gray-600 mt-1">Catatan admin: {{ $returnRequest->admin_note }}</p>
                            @endif
                        </div>
                    @else
                        <div x-data="{ open: false }" class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                            <button type="button" @click="open = !open"
                                class="font-bold bg-orange-600 hover:bg-orange-700 text-white py-3 px-6 rounded-full transition">
                                <i class="fas fa-rotate-left mr-2"></i>Ajukan Retur
                            </button>
                            <form x-show="open" x-transition
                                action="{{ route('product_returns.store', $product_transaction->id) }}" method="POST"
                                class="mt-4 space-y-3">
                                @csrf
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Alasan Retur</label>
                                    <select name="reason" required
                                        class="w-full border rounded-lg px-4 py-2 text-sm">
                                        <option value="">Pilih alasan...</option>
                                        <option value="produk_cacat">Produk cacat / rusak</option>
                                        <option value="salah_produk">Produk tidak sesuai pesanan</option>
                                        <option value="salah_jumlah">Jumlah kurang / lebih</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="text-sm font-semibold text-gray-700">Keterangan</label>
                                    <textarea name="description" rows="3" maxlength="1000"
                                        class="w-full border rounded-lg px-4 py-2 text-sm"
                                        placeholder="Jelaskan kondisi pesanan..."></textarea>
                                </div>
                                <button type="submit"
                                    class="bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 px-6 rounded-full text-sm">
                                    Kirim Pengajuan
                                </button>
                            </form>
                        </div>
                    @endif
                @endif

                <hr class="my-4">
                <div class="flex flex-wrap gap-4 items-center justify-between">
                    {{-- Batal ketika masih pending --}}
                    @if ($product_transaction->status === 'pending')
                        <form action="{{ route('product_transactions.destroy', $product_transaction->id) }}" method="POST"
                            onsubmit="return confirm('Batalkan pesanan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="font-bold bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-full transition">
                                <i class="fas fa-times-circle mr-2"></i>Batalkan Pesanan
                            </button>
                        </form>
                    @else
                        <span class="text-sm text-gray-400">
                            Pesanan berstatus <strong>{{ $product_transaction->statusLabel() }}</strong> tidak dapat dibatalkan.
                        </span>
                    @endif

                    {{-- Contact admin via WA (opsional, bukan pengganti tracking) --}}
                    <a href="https://wa.me/{{ \App\Support\WaNotifier::phoneToWa(\App\Support\StoreSettings::waContact()) }}?text={{ urlencode('Halo Admin, saya ingin tanya soal pesanan #' . $product_transaction->id . '.') }}"
                        target="_blank"
                        class="w-fit font-bold bg-indigo-700 text-white py-3 px-6 rounded-full hover:bg-indigo-900 transition">
                        <i class="fab fa-whatsapp mr-2"></i>Hubungi Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layout-front>