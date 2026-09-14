@if ($errors->any())
    <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-lg px-4 py-3 mb-6" role="alert">
        <i class="fas fa-circle-exclamation mr-2"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <p class="text-sm font-medium text-gray-500">Pendapatan</p>
            <p class="text-2xl font-bold text-gray-800">{{ rupiah($revenue) }}</p>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <p class="text-sm font-medium text-gray-500">Total Pesanan</p>
            <p class="text-2xl font-bold text-gray-800">{{ $orderCount }}</p>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <p class="text-sm font-medium text-gray-500">Order Terbayar</p>
            <p class="text-2xl font-bold text-gray-800">{{ $paidOrderCount }}</p>
        </div>
    </div>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <p class="text-sm font-medium text-gray-500">Rata-rata / Pesanan</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ rupiah($paidOrderCount > 0 ? round($revenue / $paidOrderCount) : 0) }}</p>
        </div>
    </div>
</div>

<div class="x-report-results">
    {{-- Penjualan per Produk --}}
    <x-ui.table title="Penjualan per Produk" :headers="['Produk', 'Kategori', 'Qty Terjual', 'Revenue']"
        class="mb-6">
        @forelse ($productSales as $row)
            <tr>
                <td class="cell"><span class="font-medium text-gray-900">{{ $row->product->name ?? 'Produk' }}</span></td>
                <td class="cell cell-soft">{{ $row->product->category?->name ?? '-' }}</td>
                <td class="cell">{{ $row->total_qty }}</td>
                <td class="cell">{{ rupiah($row->total_revenue) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="cell text-center text-gray-500">Tidak ada penjualan pada rentang ini.</td>
            </tr>
        @endforelse
    </x-ui.table>

    {{-- Penjualan Harian --}}
    <x-ui.table title="Penjualan Harian" :headers="['Tanggal', 'Order', 'Order Dibayar', 'Revenue']"
        class="mb-6">
        @forelse ($dailySales as $row)
            <tr>
                <td class="cell cell-soft">{{ \Carbon\Carbon::parse($row->date)->idShort() }}</td>
                <td class="cell">{{ $row->total_orders }}</td>
                <td class="cell">{{ $row->paid_orders }}</td>
                <td class="cell">{{ rupiah($row->revenue) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="cell text-center text-gray-500">Belum ada data.</td>
            </tr>
        @endforelse
    </x-ui.table>

    {{-- Riwayat Mutasi Stok --}}
    <x-ui.table title="Riwayat Mutasi Stok" :headers="['Produk', 'Kategori', 'Masuk', 'Keluar', 'Bersih']">
        @forelse ($mutationReport as $row)
            <tr>
                <td class="cell"><span class="font-medium text-gray-900">{{ $row['name'] }}</span></td>
                <td class="cell cell-soft">{{ $row['category'] ?? '-' }}</td>
                <td class="cell"><x-ui.badge class="bg-green-100 text-green-800">+{{ $row['in'] }}</x-ui.badge></td>
                <td class="cell"><x-ui.badge class="bg-red-100 text-red-800">-{{ $row['out'] }}</x-ui.badge></td>
                <td class="cell">{{ $row['net'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="cell text-center text-gray-500">Tidak ada mutasi stok pada rentang ini.</td>
            </tr>
        @endforelse
    </x-ui.table>
</div>