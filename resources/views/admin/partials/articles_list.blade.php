<div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
    @forelse($articles as $article)
        <div class="item-card flex flex-row justify-between items-center">
            <div class="flex flex-row items-center gap-x-2">
                @if ($article->featured_image)
                    <img src="{{ Storage::url($article->featured_image) }}" alt="{{ $article->title }}"
                        class="w-[50px] h-[50px] object-cover rounded">
                @endif
                <div>
                    <h3 class="text-xl font-bold text-indigo-900">{{ $article->title }}</h3>
                    <p class="text-base text-slate-500">
                        {{ 'Category: ' . $article->category?->name ?? 'No Category' }} ·
                        {{ $article->user?->name ?? 'Unknown' }} ·
                        {{ $article->created_at->format('d M Y H:i') }}
                    </p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ Str::limit(strip_tags($article->content), 100, '...') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-row items-center gap-x-3">
                <button type="button" @click="openEdit('{{ $article->id }}')"
                    class="font-bold py-3 px-5 rounded-full text-white bg-yellow-500">Edit</button>
                <button type="button" @click="openDelete('{{ $article->id }}')"
                    class="font-bold py-3 px-5 rounded-full text-white bg-red-700">Delete</button>
            </div>
        </div>
    @empty
        <p>Ups, belum ada artikel nih!</p>
    @endforelse
</div>
<div class="mt-5">
    {{ $articles->appends(['search' => $search])->links() }}
</div>