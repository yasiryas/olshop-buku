<x-ui.table :headers="['Artikel', 'Kategori', 'Penulis', 'Tanggal', 'Aksi']"
    :footer="$articles->hasPages() ? $articles->appends(['search' => $search])->links() : null">
    @forelse ($articles as $article)
        <tr>
            <td class="cell">
                <div class="flex items-center gap-3 max-w-md">
                    @if ($article->featured_image)
                        <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                            class="w-10 h-10 object-cover rounded shrink-0">
                    @endif
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $article->title }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ Str::limit(strip_tags($article->content), 80, '...') }}</p>
                    </div>
                </div>
            </td>
            <td class="cell cell-soft">{{ $article->category?->name ?? '-' }}</td>
            <td class="cell cell-soft">{{ $article->user?->name ?? '-' }}</td>
            <td class="cell cell-soft">{{ $article->created_at->idShort() }}</td>
            <td class="cell">
                <div class="flex items-center gap-2">
                    <x-ui.pill as="a" href="{{ route('admin.articles.edit', $article) }}" color="btn-pill-warning">
                        <i class="fas fa-pen text-xs"></i> Edit
                    </x-ui.pill>
                    <x-ui.pill as="button" type="button" color="btn-pill-danger"
                        @click="openDelete('{{ $article->id }}')">
                        <i class="fas fa-trash text-xs"></i> Hapus
                    </x-ui.pill>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="cell text-center text-gray-500">Belum ada artikel.</td>
        </tr>
    @endforelse
</x-ui.table>