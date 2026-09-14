<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.table :headers="['Nama', 'Email', 'Total Order', 'Total Belanja', 'Bergabung', 'Aksi']"
                :footer="$customers->hasPages() ? $customers->links() : null">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="cell"><span class="font-medium text-gray-900">{{ $customer->name }}</span></td>
                        <td class="cell cell-soft">{{ $customer->email }}</td>
                        <td class="cell cell-soft">{{ $customer->total_orders }}</td>
                        <td class="cell">{{ rupiah($customer->total_spent) }}</td>
                        <td class="cell cell-soft">{{ $customer->created_at->idShort() }}</td>
                        <td class="cell">
                            <x-ui.pill as="a" href="{{ route('admin.customers.show', $customer) }}" color="btn-pill-primary">
                                <i class="fas fa-eye text-xs"></i> Detail
                            </x-ui.pill>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="cell text-center text-gray-500">Belum ada pelanggan.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>