<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        @if ($inactiveCount > 0)
            <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-sm">
                <i class="fas fa-exclamation-triangle mr-2"></i>{{ $inactiveCount }} akun staff sedang dinonaktifkan.
            </div>
        @endif

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($staff as $user)
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ ucfirst($user->roles->first()?->name) }}</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Aktif</span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-700">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm">
                            <form action="{{ route('admin.staff.toggle', $user) }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="font-bold text-sm {{ $user->is_active ? 'text-red-600 hover:text-red-800' : 'text-green-600 hover:text-green-800' }}">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada staff.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-5">
            {{ $staff->appends(['search' => $search ?? null])->links() }}
        </div>
    </div>
</div>