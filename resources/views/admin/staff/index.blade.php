<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Staff') }}
            </h2>
            <form method="GET" action="{{ route('admin.staff.index') }}" class="flex gap-x-3"
                x-data="searchableList('{{ route('admin.staff.index') }}', 'results-staff')"
                @submit.prevent="search()">
                <input type="text" name="search" placeholder="Cari nama, email, peran..." value="{{ request('search') }}"
                    x-model="keyword" @input.debounce.500ms="search()"
                    class="border-2 text-slate-400 rounded-full px-4 py-2">
            </form>
            <button type="button" @click="$dispatch('open-modal', 'add-staff')"
                class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Tambah Staff</button>
        </div>
    </x-slot>

    <div class="py-12"
        x-data="{
            adding: false,
            submitAdd(form) {
                if (this.adding) return;
                this.adding = true;
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => {
                    if (!res.ok && !res.redirected) throw new Error('Gagal menambah staff');
                    form.reset();
                    this.$dispatch('close-modal', 'add-staff');
                    return this.refreshList();
                }).catch(() => {
                    this.adding = false;
                    alert('Gagal menambah staff. Cek kembali data (email mungkin sudah terdaftar).');
                });
            },
            refreshList() {
                return fetch('{{ route('admin.staff.index') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => res.text()).then(html => {
                    const el = document.getElementById('results-staff');
                    el.innerHTML = html;
                    Alpine.initTree(el);
                    this.adding = false;
                });
            }
        }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
                    {{ session('success') }}
                </div>
            @endif

            <div id="results-staff">
                @include('admin.partials.staff_list')
            </div>
        </div>

        <x-modal name="add-staff" maxWidth="lg" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">{{ __('Tambah Staff') }}</h2>
                <form method="POST" action="{{ route('admin.staff.store') }}" class="space-y-5"
                    @submit.prevent="submitAdd($el)">
                    @csrf
                    <div>
                        <label class="font-semibold">Nama</label>
                        <input type="text" name="name" required class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Email</label>
                        <input type="email" name="email" required class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Password</label>
                        <input type="password" name="password" required class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" required class="w-full border rounded-lg px-4 py-2">
                    </div>
                    <div>
                        <label class="font-semibold">Peran</label>
                        <select name="role" required class="w-full border rounded-lg px-4 py-2">
                            <option value="admin">Admin</option>
                            <option value="penulis">Penulis</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="$dispatch('close-modal', 'add-staff')"
                            class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                            Batal
                        </button>
                        <button type="submit" class="bg-indigo-700 text-white font-semibold py-1.5 px-4 rounded-full hover:bg-indigo-900">
                            Simpan Staff
                        </button>
                    </div>
                </form>
            </div>
        </x-modal>
    </div>
</x-app-layout>