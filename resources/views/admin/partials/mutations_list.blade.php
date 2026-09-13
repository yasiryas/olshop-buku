<x-ui.table :headers="['Produk', 'Tipe', 'Jumlah', 'Tanggal', 'Deskripsi']"
    :footer="$mutations->hasPages() ? $mutations->appends(['search' => $search])->links() : null">
    @forelse ($mutations as $m)
        <tr>
            <td class="cell"><span class="font-medium text-gray-900">{{ $m->product->name }}</span></td>
            <td class="cell">
                <x-ui.badge :class="$m->type === 'in' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'">
                    {{ strtoupper($m->type) }}
                </x-ui.badge>
            </td>
            <td class="cell"><span class="font-bold text-indigo-700">{{ $m->quantity }}</span></td>
            <td class="cell cell-soft">{{ $m->created_at->idDateTime() }}</td>
            <td class="cell cell-soft whitespace-normal">{{ $m->description ?? '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="cell text-center text-gray-500">Belum ada history stock.</td>
        </tr>
    @endforelse
</x-ui.table>