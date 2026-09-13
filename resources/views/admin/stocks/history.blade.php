<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Riwayat Stok — {{ $product->name }}
            </h2>
            <a href="{{ route('stocks.allHistory') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Semua Riwayat</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-ui.table :headers="['Tanggal', 'Tipe', 'Jumlah', 'Deskripsi']">
                @forelse ($mutations as $m)
                    <tr>
                        <td class="cell cell-soft">{{ $m->created_at->idDateTime() }}</td>
                        <td class="cell">
                            <x-ui.badge :class="$m->type === 'in' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'">
                                {{ strtoupper($m->type) }}
                            </x-ui.badge>
                        </td>
                        <td class="cell"><span class="font-bold text-indigo-700">{{ $m->quantity }}</span></td>
                        <td class="cell cell-soft whitespace-normal">{{ $m->description ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="cell text-center text-gray-500">Belum ada riwayat stok.</td>
                    </tr>
                @endforelse
            </x-ui.table>
        </div>
    </div>
</x-app-layout>