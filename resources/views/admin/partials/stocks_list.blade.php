<x-ui.table :headers="['Produk', 'Kategori', 'Harga', 'Stok', 'Aksi']"
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
                <span class="font-bold text-indigo-700">{{ $product->stock }}</span>
            </td>
            <td class="cell">
                <div class="flex items-center gap-2">
                    <x-ui.pill as="button" type="button" color="btn-pill-primary"
                        @click="openModal('{{ $product->id }}','in')">
                        <i class="fas fa-arrow-down text-xs"></i> Stock In
                    </x-ui.pill>
                    <x-ui.pill as="button" type="button" color="btn-pill-danger"
                        @click="openModal('{{ $product->id }}','out')">
                        <i class="fas fa-arrow-up text-xs"></i> Stock Out
                    </x-ui.pill>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="cell text-center text-gray-500">Belum ada produk.</td>
        </tr>
    @endforelse
</x-ui.table>