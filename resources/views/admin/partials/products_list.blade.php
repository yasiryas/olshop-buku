<x-ui.table :headers="['Produk', 'Kategori', 'Harga', 'Aksi']"
    :footer="$products->hasPages() ? $products->appends(['search' => $search])->links() : null">
    @forelse ($products as $product)
        <tr>
            <td class="cell">
                <div class="flex items-center gap-3">
                    <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}"
                        class="w-10 h-10 object-cover rounded">
                    <span class="font-medium text-gray-900">{{ $product->name }}</span>
                </div>
            </td>
            <td class="cell cell-soft">{{ $product->category?->name ?? '-' }}</td>
            <td class="cell">{{ rupiah($product->price) }}</td>
            <td class="cell">
                <div class="flex items-center gap-2">
                    <x-ui.pill as="button" type="button" color="btn-pill-warning"
                        @click="openEdit('{{ $product->id }}')">
                        <i class="fas fa-pen text-xs"></i> Edit
                    </x-ui.pill>
                    <x-ui.pill as="button" type="button" color="btn-pill-danger"
                        @click="openDelete('{{ $product->id }}')">
                        <i class="fas fa-trash text-xs"></i> Hapus
                    </x-ui.pill>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="cell text-center text-gray-500">Belum ada produk.</td>
        </tr>
    @endforelse
</x-ui.table>