<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Articles') }}
            </h2>
            <form method="GET" action="{{ route('admin.articles.index') }}">
                <input type="text" name="search" placeholder="Search articles..." value="{{ request('search') }}"
                    class="border-2 text-slate-400 rounded-full px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-indigo-700 text-white rounded-full"><i
                        class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <a href="{{ route('admin.articles.create') }}"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">Add article</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white flex flex-col gap-y-5 p-10 overflow-hidden shadow-sm sm:rounded-lg">
                @forelse($articles as $article)
                    <div class="item-card flex flex-row justify-between items-center">
                        <div class="flex flex-row items-center gap-x-4">
                            <div>
                                <h3 class="text-xl font-bold text-indigo-900">{{ $article->title }}</h3>
                                <p class="text-base text-slate-500">
                                    {{ 'Category: ' . $article->category?->name ?? 'No Category' }}<br>
                                    Author: {{ $article->user?->name ?? 'Unknown' }}<br>

                                </p>
                                <small class="text-gray-500">Created at:
                                    {{ $article->created_at->format('d M Y H:i') }}</small>
                                </p>
                            </div>
                        </div>
                        <p class="text-base text-slate-500">
                            {{ Str::limit(strip_tags($article->content), 100, '...') }}<br>
                        </p>
                        <div class="flex flex-row items-center gap-x-3">
                            <a href="{{ route('admin.articles.edit', $article) }}"
                                class="font-bold py-3 px-5 rounded-full text-white bg-yellow-500">Edit</a>
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}">
                                @csrf
                                @method('DELETE')
                                <button class="font-bold py-3 px-5 rounded-full text-white bg-red-700">Delete</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p>Ups, belum ada artikel nih!</p>
                @endforelse
            </div>
            <div class="mt-5">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
