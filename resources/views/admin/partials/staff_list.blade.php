@if ($inactiveCount > 0)
    <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-sm">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ $inactiveCount }} akun staff sedang dinonaktifkan.
    </div>
@endif

<x-ui.table :headers="['Nama', 'Email', 'Peran', 'Status', 'Aksi']"
    :footer="$staff->hasPages() ? $staff->links() : null">
    @forelse ($staff as $user)
        <tr>
            <td class="cell"><span class="font-medium text-gray-900">{{ $user->name }}</span></td>
            <td class="cell cell-soft">{{ $user->email }}</td>
            <td class="cell cell-soft">{{ ucfirst($user->roles->first()?->name) }}</td>
            <td class="cell">
                @if ($user->is_active)
                    <x-ui.badge class="bg-green-100 text-green-800">Aktif</x-ui.badge>
                @else
                    <x-ui.badge class="bg-gray-200 text-gray-700">Nonaktif</x-ui.badge>
                @endif
            </td>
            <td class="cell">
                <x-ui.pill as="button" type="button"
                    :color="$user->is_active ? 'btn-pill-danger' : 'btn-pill-success'"
                    @click="openToggle({{ \Illuminate\Support\Js::from(['id' => $user->id, 'name' => $user->name, 'url' => route('admin.staff.toggle', $user), 'is_active' => (bool) $user->is_active]) }})">
                    <i :class="{{ $user->is_active ? "'fa-ban'" : "'fa-circle-check'" }}" class="fas text-xs"></i>
                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </x-ui.pill>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="cell text-center text-gray-500">Belum ada staff.</td>
        </tr>
    @endforelse
</x-ui.table>