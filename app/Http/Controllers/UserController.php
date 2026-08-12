<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // 1. DAFTAR SEMUA USER
    public function index()
    {
        $this->ensureAdmin();

        // Tampilkan semua user kecuali Admin sendiri (biar ga kehapus ga sengaja)
        $users = User::where('role', '!=', 'admin')->latest()->get();

        return view('users.index', compact('users'));
    }

    // 2. FORM TAMBAH USER
    public function create()
    {
        $this->ensureAdmin();

        // Kita butuh data atasan untuk dropdown
        // Ambil semua Kabid (untuk calon atasan Kasi)
        $kabids = User::where('role', 'kabid')->get();

        // Ambil semua Kasi (untuk calon atasan Staf)
        $kasis = User::where('role', 'kasi')->get();

        return view('users.create', compact('kabids', 'kasis'));
    }

    // 3. SIMPAN USER BARU
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'kabid', 'kasi', 'staff'])],
            'jabatan' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:users,id'],
            // parent_id boleh null (jika Kabid)
        ]);

        User::create([
            'name' => $request->name,
            'nip' => $request->nip,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'jabatan' => $request->jabatan,
            'parent_id' => $request->parent_id, // ID Atasan yang dipilih
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    // 4. HAPUS USER
    public function destroy($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        abort_if($user->role === 'admin', 403, 'Akun Admin tidak boleh dihapus dari halaman ini.');

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User dihapus!');
    }

    private function ensureAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403, 'Manajemen pengguna hanya untuk Admin.');
    }
}
