<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Pelanggan: ') . $user->name }}
            </h2>
            <a href="{{ route('admin.customers.index') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="text-lg font-bold text-gray-900">{{ $user->email }}</p>
                    <p class="mt-2 text-sm text-gray-500">Bergabung sejak {{ $user->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Transaksi</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($transactions as $transaction)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">#{{ $transaction->id }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ $transaction->created_at->format('d M Y') }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($transaction->total_amount) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $transaction->statusBadgeColor() }} text-white">{{ $transaction->statusLabel() }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <a href="{{ route('product_transactions.show', $transaction) }}"
                                                class="font-bold text-indigo-700 hover:text-indigo-900">Detail</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-5">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>