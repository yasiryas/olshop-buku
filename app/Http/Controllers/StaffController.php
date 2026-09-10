<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    private const STAFF_ROLES = ['admin', 'penulis'];

    public function index()
    {
        $staff = User::whereHas('roles', fn ($q) => $q->whereIn('name', self::STAFF_ROLES))
            ->with('roles')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.staff.index', [
            'staff' => $staff,
            'inactiveCount' => $staff->where('is_active', false)->count(),
        ]);
    }

    public function create()
    {
        return view('admin.staff.create');
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