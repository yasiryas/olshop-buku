<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row w-full justify-between items-start md:items-center gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Kelola Staff') }}
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <form method="GET" action="{{ route('admin.staff.index') }}"
                    x-data="searchableList('{{ route('admin.staff.index') }}', 'results-staff')"
                    @submit.prevent="search()">
                    <input type="text" name="search" placeholder="Cari nama, email, peran..." value="{{ request('search') }}"
                        x-model="keyword" @input.debounce.500ms="search()"
                        class="border-2 border-gray-300 text-gray-700 rounded-full px-4 py-2 text-sm">
                </form>
                <button type="button" x-data="" @click="$dispatch('open-modal', 'add-staff')"
                    class="font-semibold py-2 px-4 rounded-full text-white bg-indigo-700">Tambah Staff</button>
            </div>
        </div>
    </x-slot>

    <div class="py-12"
    x-data="{
            adding: false,
            toggleStaff: null,
            openToggle(staff) {
                this.toggleStaff = staff;
                this.$dispatch('open-modal', 'toggle-staff');
            },
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
                    return this.refreshList().then(() => {
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: { type: 'success', message: 'Staff baru berhasil ditambahkan.' }
                        }));
                    });
                }).catch(() => {
                    this.adding = false;
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: { type: 'error', message: 'Gagal menambah staff. Cek kembali data (email mungkin sudah terdaftar).' }
                    }));
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

        {{-- Modal Konfirmasi Aktif/Nonaktif Staf --}}
        <x-modal name="toggle-staff" maxWidth="md" focusable>
            <div class="p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-2"
                    x-text="toggleStaff ? (toggleStaff.is_active ? 'Nonaktifkan Staf' : 'Aktifkan Staf') : ''"></h2>
                <template x-if="toggleStaff">
                    <div>
                        <p class="text-sm text-gray-600 mb-4">
                            <template x-if="toggleStaff.is_active">
                                <span>Nonaktifkan akun <b x-text="toggleStaff.name"></b>? Staf tidak akan bisa login sampai diaktifkan kembali.</span>
                            </template>
                            <template x-if="!toggleStaff.is_active">
                                <span>Aktifkan kembali akun <b x-text="toggleStaff.name"></b>? Staf bisa login kembali.</span>
                            </template>
                        </p>
                        <div class="flex justify-end gap-3">
                            <button type="button" @click="$dispatch('close-modal', 'toggle-staff')"
                                class="px-3 py-1.5 bg-gray-300 hover:bg-gray-400 rounded-full">
                                Batal
                            </button>
                            <form method="POST" :action="toggleStaff.url">
                                @csrf
                                <button type="submit"
                                    :class="toggleStaff.is_active ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
                                    class="px-3 py-1.5 text-white rounded-full">
                                    <span x-text="toggleStaff.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </x-modal>
    </div>
</x-app-layout>