<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage product') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.products.index') }}" class="flex"
                    x-data="searchableList('{{ route('admin.products.index') }}', 'results-products')"
                    @submit.prevent="search()">
                    <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}"
                        x-model="keyword" @input.debounce.500ms="search()"
                        class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                </form>
                <button type="button" x-data="" @click="$dispatch('open-modal', 'add-product')"
                    class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Add
                    product</button>
            </div>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            item: null,
            editDataUrl: '{{ route('admin.products.edit-data', ['product' => '__ID__']) }}',
            openEdit(id) {
                fetch(this.editDataUrl.replace('__ID__', id), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(row => {
                        this.item = row;
                        this.$dispatch('open-modal', 'edit-product');
                    });
            },
            deleteId: null,
            openDelete(id) {
                this.deleteId = id;
                this.$dispatch('open-modal', 'delete-product');
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
                    this.$dispatch('close-modal', 'edit-product');
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
                    this.$dispatch('close-modal', 'delete-product');
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
                    this.refreshList('results-products', '{{ route('admin.products.index') }}');
                }, 30000);
            },
            destroy() {
                clearInterval(this.pollTimer);
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="results-products">
                @include('admin.partials.products_list')
            </div>
        </div>

        <x-modal name="add-product" maxWidth="lg" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Add New Product') }}</h2>
                <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                            :value="old('name')" required autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="price" :value="__('Price (Rp)')" />
                        <x-text-input id="price" class="block mt-1 w-full" type="text" name="price"
                            placeholder="Contoh: 50.000" :value="old('price')" required autocomplete="price" />
                        <x-input-error :messages="$errors->get('price')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="category" :value="__('Category')" />
                        <select name="category_id" id="category_id"
                            class="py-2 rounded-lg w-full border-slate-300"
                            @if (($categories ?? collect())->count() > 4) x-select2 @endif>
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
                        <x-input-label for="about" :value="__('About')" />
                        <textarea name="about" id="about" cols="30" rows="5"
                            class="border rounded-lg w-full border-slate-300"></textarea>
                        <x-input-error :messages="$errors->get('about')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="photo" :value="__('Photo')" />
                        <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo" required />
                        <x-input-error :messages="$errors->get('photo')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-4">
                        <x-primary-button class="ms-4">
                            {{ __('Add New Product') }}
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </x-modal>

        @push('scripts')
            <script>
                const priceInput = document.getElementById('price');
                priceInput.addEventListener('input', function(e) {
                    let value = e.target.value.replace(/\D/g, '');
                    value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    e.target.value = value;
                });
            </script>
        @endpush

        <x-modal name="edit-product" maxWidth="lg" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Edit Product') }}</h2>
                <template x-if="item">
                    <form method="POST" :action="`/admin/products/${item.id}`" enctype="multipart/form-data"
                        @submit.prevent="submitEdit($el, 'results-products', '{{ route('admin.products.index') }}')">
                        @csrf
                        @method('PUT')
                        <div>
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                x-model="item.name" required autocomplete="name" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="price" :value="__('Price (Rp)')" />
                            <x-text-input id="price-edit" class="block mt-1 w-full" type="text" name="price"
                                x-model="item.price" @input="item.price = item.price.replace(/\D/g, '').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"
                                required />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="category" :value="__('Category')" />
<select name="category_id" id="category_id" x-model="item.category_id"
                            class="py-2 rounded-lg w-full border-slate-300"
                            @if (($categories ?? collect())->count() > 4) x-select2 @endif>
                            <option value="">Select Category</option>
                            @forelse ($categories ?? [] as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @empty
                            @endforelse
                        </select>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="about" :value="__('About')" />
                            <textarea name="about" id="about" cols="30" rows="6" x-model="item.about"
                                class="border rounded-lg w-full border-slate-300"></textarea>
                        </div>
                        <div class="mt-4">
                            <img :src="item.photo" alt="" class="w-[50px] h-[50px] mb-2">
                            <x-input-label for="photo" :value="__('Photo')" />
                            <x-text-input id="photo" class="block mt-1 w-full" type="file" name="photo" />
                        </div>
                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Product') }}
                            </x-primary-button>
                        </div>
                    </form>
                </template>
            </div>
        </x-modal>

        <x-modal name="delete-product" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2">Konfirmasi Hapus</h2>
                <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak
                    dapat dibatalkan.</p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="$dispatch('close-modal', 'delete-product')"
                        class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                        Batal
                    </button>
                    <form method="POST" :action="`/admin/products/${deleteId}`"
                        @submit.prevent="submitDelete($el, 'results-products', '{{ route('admin.products.index') }}')">
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
