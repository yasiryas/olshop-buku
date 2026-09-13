<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Artikel') }}
            </h2>
            <a href="{{ route('admin.articles.index') }}"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Kelola
                Artikel</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.articles.update', $article->id) }}"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div>
                            <x-input-label for="title" :value="__('Judul')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                :value="old('title', $article->title)" required autofocus autocomplete="title" />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        <!-- Featured Image -->
                        <div class="mt-4">
                            <x-input-label for="featured_image" :value="__('Gambar Utama')" />

                            @if ($article->featured_image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $article->featured_image) }}"
                                        alt="Current featured image" class="h-32">
                                    <p class="text-sm text-gray-500 mt-1">Gambar saat ini</p>
                                </div>
                            @endif

                            <x-text-input id="featured_image" class="block mt-1 w-full" type="file"
                                name="featured_image" autocomplete="featured_image" />
                            <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
                        </div>

                        <!-- Category -->
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Kategori')" />
                            <select name="category_id" id="category_id" x-select2 class="mt-1 block w-full">
                                <option value="">Pilih Kategori</option>
                                @forelse ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @empty
                                @endforelse
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                        </div>

                        <!--Content-->
                        <div class="mt-4">
                            <x-input-label for="content" :value="__('Konten')" />
                            <textarea name="content" id="content"
                                class="hidden">{{ old('content', $article->content) }}</textarea>
                            <div id="content-editor" x-data="richEditor('content')"
                                class="border border-slate-300 rounded-lg bg-white"></div>
                            <x-input-error :messages="$errors->get('content')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Perbarui Artikel') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
