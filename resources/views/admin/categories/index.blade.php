<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage categories') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.categories.index') }}"
                    x-data="searchableList('{{ route('admin.categories.index') }}', 'results-categories')"
                    @submit.prevent="search()">
                    <input type="text" name="search" placeholder="Search categories..." value="{{ request('search') }}"
                        x-model="keyword" @input.debounce.500ms="search()"
                        class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                </form>
                <button type="button" x-data="" @click="$dispatch('open-modal', 'add-category')"
                    class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Add
                    category</button>
            </div>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            item: null,
            editDataUrl: '{{ route('admin.categories.edit-data', ['category' => '__ID__']) }}',
            openEdit(id) {
                fetch(this.editDataUrl.replace('__ID__', id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(row => {
                        this.item = row;
                        this.$dispatch('open-modal', 'edit-category');
                    });
            },
            deleteId: null,
            openDelete(id) {
                this.deleteId = id;
                this.$dispatch('open-modal', 'delete-category');
            },
            addErrors: {},
            adding: false,
            submitAdd(form, resultsId, listUrl) {
                if (this.adding) return;
                this.addErrors = {};
                const formData = new FormData(form);
                this.adding = true;
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    if (res.status === 422) {
                        return res.json().then(data => {
                            this.addErrors = data.errors || {};
                            this.adding = false;
                        });
                    }
                    if (!res.ok && !res.redirected) throw new Error('Gagal menyimpan');
                    return res.json().then(data => {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: data.message || 'Kategori berhasil dibuat.', type: 'success' }
                        }));
                        this.$dispatch('close-modal', 'add-category');
                        form.reset();
                        this.addErrors = {};
                        return this.refreshList(resultsId, listUrl);
                    });
                }).catch(() => {
                    this.adding = false;
                });
            },
            editErrors: {},
            updating: false,
            submitEdit(form, resultsId, listUrl) {
                if (this.updating) return;
                this.editErrors = {};
                const formData = new FormData(form);
                this.updating = true;
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    if (res.status === 422) {
                        return res.json().then(data => {
                            this.editErrors = data.errors || {};
                            this.updating = false;
                        });
                    }
                    if (!res.ok && !res.redirected) throw new Error('Gagal menyimpan');
                    return res.json().then(data => {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: data.message || 'Kategori berhasil diperbarui.', type: 'success' }
                        }));
                        this.$dispatch('close-modal', 'edit-category');
                        this.editErrors = {};
                        return this.refreshList(resultsId, listUrl);
                    });
                }).catch(() => {
                    this.updating = false;
                });
            },
            deleteErrors: {},
            deleting: false,
            submitDelete(form, resultsId, listUrl) {
                if (this.deleting) return;
                this.deleteErrors = {};
                this.deleting = true;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    if (res.status === 422) {
                        return res.json().then(data => {
                            this.deleteErrors = data.errors || { system_error: [data.message] };
                            this.deleting = false;
                        });
                    }
                    if (!res.ok && !res.redirected) throw new Error('Gagal menghapus');
                    return res.json().then(data => {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { message: data.message || 'Kategori berhasil dihapus.', type: 'success' }
                        }));
                        this.$dispatch('close-modal', 'delete-category');
                        this.deleteErrors = {};
                        return this.refreshList(resultsId, listUrl);
                    });
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
                        this.adding = false;
                        this.deleting = false;
                        this.updating = false;
                    });
            },
            init() {
                this.$on('open-modal', (e) => {
                    if (e.detail === 'add-category') this.addErrors = {};
                    if (e.detail === 'edit-category') this.editErrors = {};
                    if (e.detail === 'delete-category') this.deleteErrors = {};
                });
                this.pollTimer = setInterval(() => {
                    if (document.hidden) return;
                    this.refreshList('results-categories', '{{ route('admin.categories.index') }}');
                }, 30000);
            },
            destroy() {
                clearInterval(this.pollTimer);
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="results-categories">
                @include('admin.partials.categories_list')
            </div>
        </div>

        <x-modal name="add-category" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Add New Category') }}</h2>
                <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data"
                    @submit.prevent="submitAdd($el, 'results-categories', '{{ route('admin.categories.index') }}')">
                    @csrf
                    <template x-if="addErrors.name || addErrors.icon || addErrors.system_error">
                        <div class="mb-4 p-3 bg-red-50 border border-red-300 rounded text-sm text-red-700">
                            <template x-if="addErrors.name"><p x-text="addErrors.name[0]"></p></template>
                            <template x-if="addErrors.icon"><p x-text="addErrors.icon[0]"></p></template>
                            <template x-if="addErrors.system_error"><p x-text="addErrors.system_error[0]"></p></template>
                        </div>
                    </template>
                    <div>
                        <x-input-label for="add-name" :value="__('Name')" />
                        <x-text-input id="add-name" class="block mt-1 w-full" type="text" name="name"
                            required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="add-icon" :value="__('Icon')" />
                        <input id="add-icon" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" type="file" name="icon" accept="image/jpeg,image/png,image/jpg,image/svg+xml" required />
                        <x-input-error :messages="$errors->get('icon')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ms-4" x-bind:disabled="adding">
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
                        <template x-if="editErrors.name || editErrors.icon || editErrors.system_error">
                            <div class="mb-4 p-3 bg-red-50 border border-red-300 rounded text-sm text-red-700">
                                <template x-if="editErrors.name"><p x-text="editErrors.name[0]"></p></template>
                                <template x-if="editErrors.icon"><p x-text="editErrors.icon[0]"></p></template>
                                <template x-if="editErrors.system_error"><p x-text="editErrors.system_error[0]"></p></template>
                            </div>
                        </template>
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
                    tidak dapat dibatalkan. Kategori yang masih berisi produk tidak dapat dihapus.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'delete-category')"
                        class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                        Batal
                    </button>
                    <form method="POST" :action="`/admin/categories/${deleteId}`"
                        @submit.prevent="submitDelete($el, 'results-categories', '{{ route('admin.categories.index') }}')">
                        @csrf
                        @method('DELETE')
                        <template x-if="deleteErrors.system_error">
                            <div class="mb-3 p-3 bg-red-50 border border-red-300 rounded text-sm text-red-700">
                                <p x-text="deleteErrors.system_error[0]"></p>
                            </div>
                        </template>
                        <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-full">
                            Hapus
                        </button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
