<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pelanggan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Order</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Belanja</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($customers as $customer)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->email }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->total_orders }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">Rp {{ number_format($customer->total_spent) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ $customer->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('admin.customers.show', $customer) }}"
                                            class="font-bold text-indigo-700 hover:text-indigo-900">Detail</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada pelanggan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-5">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>