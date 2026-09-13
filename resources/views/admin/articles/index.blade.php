<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manage Articles') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.articles.index') }}"
                    x-data="searchableList('{{ route('admin.articles.index') }}', 'results-articles')"
                    @submit.prevent="search()">
                    <input type="text" name="search" placeholder="Search articles..." value="{{ request('search') }}"
                        x-model="keyword" @input.debounce.500ms="search()"
                        class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                </form>
                <a href="{{ route('admin.articles.create') }}"
                    class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Buat Artikel</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            deleteId: null,
            openDelete(id) {
                this.deleteId = id;
                this.$dispatch('open-modal', 'delete-article');
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
