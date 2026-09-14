<x-ui.table :headers="['Nama', 'Tanggal Dibuat', 'Aksi']"
    :footer="$categories->hasPages() ? $categories->appends(['search' => $search])->links() : null">
    @forelse ($categories as $category)
        <tr>
            <td class="cell">
                <div class="flex items-center gap-3">
                    @if ($category->icon)
                        <img src="{{ Storage::url($category->icon) }}" alt="{{ $category->name }}"
                            class="w-10 h-10 object-cover rounded">
                    @endif
                    <span class="font-medium text-gray-900">{{ $category->name }}</span>
                </div>
            </td>
            <td class="cell cell-soft">{{ $category->created_at?->idShort() ?? '-' }}</td>
            <td class="cell">
                <div class="flex items-center gap-2">
                    <x-ui.pill as="button" type="button" color="btn-pill-warning"
                        @click="openEdit('{{ $category->id }}')">
                        <i class="fas fa-pen text-xs"></i> Edit
                    </x-ui.pill>
                    @if (($category->products_count ?? 0) > 0)
                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1.5 rounded-full"
                            title="Kategori berisi {{ $category->products_count }} produk, tidak bisa dihapus">
                            <i class="fas fa-book text-xs"></i> {{ $category->products_count }} produk
                        </span>
                    @else
                        <x-ui.pill as="button" type="button" color="btn-pill-danger"
                            @click="openDelete('{{ $category->id }}')">
                            <i class="fas fa-trash text-xs"></i> Hapus
                        </x-ui.pill>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="3" class="cell text-center text-gray-500">Belum ada kategori.</td>
        </tr>
    @endforelse
</x-ui.table>