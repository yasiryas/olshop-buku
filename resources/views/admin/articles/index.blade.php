<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Articles') }}
            </h2>
            <form method="GET" action="{{ route('admin.articles.index') }}"
                x-data="searchableList('{{ route('admin.articles.index') }}', 'results-articles')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Search articles..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 text-slate-400 rounded-full px-4 py-2">
            </form>
            <button type="button" x-data="" @click="$dispatch('open-modal', 'add-article')"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Add article</button>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            item: null,
            editDataUrl: '{{ route('admin.articles.edit-data', ['article' => '__ID__']) }}',
            openEdit(id) {
                fetch(this.editDataUrl.replace('__ID__', id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(row => {
                        this.item = row;
                        this.$dispatch('open-modal', 'edit-article');
                    });
            },
            deleteId: null,
            openDelete(id) {
                this.deleteId = id;
                this.$dispatch('open-modal', 'delete-article');
            },
            updating: false,
            submitEdit(form, resultsId, listUrl) {
                if (this.updating) return;
                const formData = new FormData(form);
                this.updating = true;
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => {
                    if (!res.ok && !res.redirected) throw new Error('Gagal menyimpan');
                    this.$dispatch('close-modal', 'edit-article');
                    return this.refreshList(resultsId, listUrl);
                }).catch(() => {
                    this.updating = false;
                });
            },
            deleting: false,
            submitDelete(form, resultsId, listUrl) {
                if (this.deleting) return;
                this.deleting = true;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => {
                    if (!res.ok && !res.redirected) throw new Error('Gagal menghapus');
                    this.$dispatch('close-modal', 'delete-article');
                    return this.refreshList(resultsId, listUrl);
                }).catch(() => {
                    this.deleting = false;
                });
            },
            refreshList(resultsId, listUrl) {
                const url = new URL(listUrl, window.location.origin);
                const searchInput = document.querySelector('input[name=search]');
                if (searchInput && searchInput.value.trim()) url.searchParams.set('search', searchInput.value.trim());
                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.text())
                    .then(html => {
                        const el = document.getElementById(resultsId);
                        el.innerHTML = html;
                        Alpine.initTree(el);
                        this.deleting = false;
                        this.updating = false;
                    });
            },
            init() {
                this.pollTimer = setInterval(() => {
                    if (document.hidden) return;
                    this.refreshList('results-articles', '{{ route('admin.articles.index') }}');
                }, 30000);
            },
            destroy() {
                clearInterval(this.pollTimer);
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="results-articles">
                @include('admin.partials.articles_list')
            </div>
        </div>

        <x-modal name="add-article" maxWidth="lg" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Add New Article') }}</h2>
                <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                            :value="old('title')" required autocomplete="title" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="featured_image" :value="__('Featured Image')" />
                        <x-text-input id="featured_image" class="block mt-1 w-full" type="file"
                            name="featured_image" />
                        <x-input-error :messages="$errors->get('featured_image')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="category" :value="__('Category')" />
                        <select name="category_id" id="category_id"
                            class="border border-slate-300 py-2 rounded-lg w-full">
                            <option value="">Select Category</option>
                            @forelse ($categories ?? [] as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="content" :value="__('Content')" />
                        <textarea name="content" id="content" cols="30" rows="6"
                            class="border rounded-lg w-full border-slate-300">{{ old('content') }}</textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ms-4">
                            {{ __('Add New Article') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    <x-modal name="edit-article" maxWidth="lg" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Edit Article') }}</h2>
                <template x-if="item">
                    <form method="POST" :action="`/admin/articles/${item.id}`" enctype="multipart/form-data"
                        @submit.prevent="submitEdit($el, 'results-articles', '{{ route('admin.articles.index') }}')">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title"
                                x-model="item.title" required autocomplete="title" />
                        </div>
                        <div class="mt-4">
                            <template x-if="item.featured_image">
                                <img :src="item.featured_image" alt="" class="w-[50px] h-[50px] mb-2">
                            </template>
                            <x-input-label for="featured_image" :value="__('Featured Image')" />
                            <x-text-input id="featured_image" class="block mt-1 w-full" type="file"
                                name="featured_image" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category')" />
<select name="category_id" id="category_id" x-model="item.category_id"
                            class="border border-slate-300 py-2 rounded-lg w-full">
                                <option value="">Select Category</option>
                                @forelse ($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="content" :value="__('Content')" />
                            <textarea name="content" id="content" cols="30" rows="6"
                                class="border rounded-lg w-full border-slate-300"
                                x-model="item.content"></textarea>
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Article') }}
                            </x-primary-button>
                        </div>
                    </form>
                </template>
            </div>
        </x-modal>

        <x-modal name="delete-article" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Hapus</h2>
                <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus artikel ini? Tindakan ini
                    tidak dapat dibatalkan.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'delete-article')"
                        class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                        Batal
                    </button>
                    <form method="POST" :action="`/admin/articles/${deleteId}`"
                        @submit.prevent="submitDelete($el, 'results-articles', '{{ route('admin.articles.index') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
