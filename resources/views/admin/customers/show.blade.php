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
                    <p class="mt-2 text-sm text-gray-500">Bergabung sejak {{ $user->created_at->idLong() }}</p>
                </div>
            </div>

            <x-ui.table title="Riwayat Transaksi" :headers="['Order ID', 'Tanggal', 'Total', 'Status', 'Aksi']"
                :footer="$transactions->hasPages() ? $transactions->links() : null">
                @forelse ($transactions as $transaction)
                    <tr>
                        <td class="cell"><span class="font-medium text-gray-900">#{{ $transaction->id }}</span></td>
                        <td class="cell cell-soft">{{ $transaction->created_at->idShort() }}</td>
                        <td class="cell">Rp {{ number_format($transaction->total_amount) }}</td>
                        <td class="cell">
                            <x-ui.badge :class="$transaction->statusBadgeColor()">
                                {{ $transaction->statusLabel() }}
                            </x-ui.badge>
                        </td>
                        <td class="cell">
                            <x-ui.pill as="a" href="{{ route('product_transactions.show', $transaction) }}" color="btn-pill-primary">
                                <i class="fas fa-eye text-xs"></i> Detail
                            </x-ui.pill>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="cell text-center text-gray-500">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>