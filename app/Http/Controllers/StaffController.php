<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private const STAFF_ROLES = ['admin', 'penulis'];

    public function index(Request $request)
    {
        $search = $request->input('search');

        $staff = User::whereHas('roles', fn ($q) => $q->whereIn('name', self::STAFF_ROLES))
            ->with('roles')
            ->when($search, fn ($q) => $q->where(function ($q2) use ($search) {
                $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('roles', fn ($qr) => $qr->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $inactiveCount = User::whereHas('roles', fn ($q) => $q->whereIn('name', self::STAFF_ROLES))
            ->where('is_active', false)
            ->count();

        if ($request->ajax()) {
            return view('admin.partials.staff_list', compact('staff', 'search', 'inactiveCount'));
        }

        return view('admin.staff.index', compact('staff', 'search', 'inactiveCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:' . implode(',', self::STAFF_ROLES),
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.staff.index')->with('success', 'Staff baru berhasil ditambahkan.');
    }

    public function toggleActive(Request $request, User $user)
    {
        abort_unless($user->hasAnyRole(self::STAFF_ROLES), 404);
        abort_if($user->id === auth()->id(), 403, 'Anda tidak bisa menonaktifkan akun sendiri.');

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        return redirect()->back()->with(
            'success',
            $user->is_active ? "Akun {$user->name} diaktifkan kembali." : "Akun {$user->name} dinonaktifkan."
        );
    }
}