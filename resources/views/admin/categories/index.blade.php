<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage categories') }}
            </h2>
            <form method="GET" action="{{ route('admin.categories.index') }}"
                x-data="searchableList('{{ route('admin.categories.index') }}', 'results-categories')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Search categories..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 text-slate-400 rounded-full px-4 py-2">
            </form>
            <button type="button" x-data="" @click="$dispatch('open-modal', 'add-category')"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">Add
                category</button>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            item: null,
            openEdit(id) {
                const row = JSON.parse(document.getElementById('category-edit-data').textContent).find(c => c.id == id);
                this.item = row;
                this.$dispatch('open-modal', 'edit-category');
            },
            deleteId: null,
            openDelete(id) {
                this.deleteId = id;
                this.$dispatch('open-modal', 'delete-category');
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
                    this.syncEditData(formData);
                    this.$dispatch('close-modal', 'edit-category');
                    return this.refreshList(resultsId, listUrl);
                }).catch(() => {
                    this.updating = false;
                });
            },
            syncEditData(formData) {
                const blob = document.getElementById('category-edit-data');
                const rows = JSON.parse(blob.textContent);
                const row = rows.find(c => c.id == this.item.id);
                if (row) row.name = formData.get('name');
                blob.textContent = JSON.stringify(rows);
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
                    this.$dispatch('close-modal', 'delete-category');
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
                    this.refreshList('results-categories', '{{ route('admin.categories.index') }}');
                }, 30000);
            },
            destroy() {
                clearInterval(this.pollTimer);
            }
        }">
        <script type="application/json"
            id="category-edit-data">{!! json_encode($editData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="results-categories">
                @include('admin.partials.categories_list')
            </div>
        </div>

        <x-modal name="add-category" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Add New Category') }}</h2>
                <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                            :value="old('name')" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="icon" :value="__('Icon')" />
                        <x-text-input id="icon" class="block mt-1 w-full" type="file" name="icon" required />
                        <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ms-4">
                            {{ __('Add New Category') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>
    <x-modal name="edit-category" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Edit Category') }}</h2>
                <template x-if="item">
                    <form method="POST" :action="`/admin/categories/${item.id}`" enctype="multipart/form-data"
                        @submit.prevent="submitEdit($el, 'results-categories', '{{ route('admin.categories.index') }}')">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                x-model="item.name" required autocomplete="name" />
                        </div>
                        <div class="mt-4">
                            <img :src="item.icon" alt="" class="w-[50px] h-[50px] mb-2">
                            <x-input-label for="icon" :value="__('Icon')" />
                            <x-text-input id="icon" class="block mt-1 w-full" type="file" name="icon" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Category') }}
                            </x-primary-button>
                        </div>
                    </form>
                </template>
            </div>
        </x-modal>

        <x-modal name="delete-category" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Hapus</h2>
                <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus kategori ini? Tindakan ini
                    tidak dapat dibatalkan.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'delete-category')"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg">
                        Batal
                    </button>
                    <form method="POST" :action="`/admin/categories/${deleteId}`"
                        @submit.prevent="submitDelete($el, 'results-categories', '{{ route('admin.categories.index') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
