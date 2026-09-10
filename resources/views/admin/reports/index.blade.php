<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">Dari</label>
                            <input type="date" name="from" value="{{ $from }}"
                                class="border rounded-lg px-4 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Sampai</label>
                            <input type="date" name="to" value="{{ $to }}"
                                class="border rounded-lg px-4 py-2 text-sm">
                        </div>
                        <button type="submit"
                            class="bg-indigo-700 text-white text-sm font-bold py-2 px-5 rounded-full">Tampilkan</button>
                        <div class="ml-auto flex flex-wrap gap-2">
                            <a href="{{ route('admin.reports.export', ['type' => 'sales', ...request()->query()]) }}"
                                class="bg-green-600 text-white text-sm font-bold py-2 px-4 rounded-full hover:bg-green-700">
                                <i class="fas fa-download mr-1"></i> Export Penjualan
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'daily', ...request()->query()]) }}"
                                class="bg-green-600 text-white text-sm font-bold py-2 px-4 rounded-full hover:bg-green-700">
                                <i class="fas fa-download mr-1"></i> Harian
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'stock', ...request()->query()]) }}"
                                class="bg-green-600 text-white text-sm font-bold py-2 px-4 rounded-full hover:bg-green-700">
                                <i class="fas fa-download mr-1"></i> Stok
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Summary --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Revenue</p>
                        <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($revenue) }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm font-medium text-gray-500">Total Order</p>
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
                        <p class="text-sm font-medium text-gray-500">Rata-rata / Order</p>
                        <p class="text-2xl font-bold text-gray-800">
                            Rp {{ number_format($paidOrderCount > 0 ? round($revenue / $paidOrderCount) : 0) }}</p>
                    </div>
                </div>
            </div>

            {{-- Penjualan per Produk --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Penjualan per Produk</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Terjual</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($productSales as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row->product->name ?? 'Produk' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row->total_qty }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($row->total_revenue) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-gray-500">Tidak ada penjualan pada rentang ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Penjualan Harian --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Penjualan Harian</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($dailySales as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row->total_orders }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($row->revenue) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-center text-gray-500">Belum ada data.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Laporan Stok --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Laporan Stok</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($stockReport as $product)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $product['name'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $product['category'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $product['stock'] }}</td>
                                        <td class="px-4 py-3">
                                            @if ($product['stock'] <= $lowStockThreshold)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Menipis</span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aman</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada produk.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>