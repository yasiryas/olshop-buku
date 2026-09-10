<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-row w-full justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Tambah Staff') }}
            </h2>
            <a href="{{ route('admin.staff.index') }}"
                class="font-bold py-3 px-5 rounded-full text-white bg-indigo-700">Kembali</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                            <ul class="list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="font-semibold">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="font-semibold">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="font-semibold">Password</label>
                            <input type="password" name="password" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="font-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="w-full border rounded-lg px-4 py-2">
                        </div>
                        <div>
                            <label class="font-semibold">Peran</label>
                            <select name="role" class="w-full border rounded-lg px-4 py-2">
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="penulis" {{ old('role') === 'penulis' ? 'selected' : '' }}>Penulis</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full bg-indigo-700 text-white font-bold py-3 rounded-xl hover:bg-indigo-900">
                            Simpan Staff
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>