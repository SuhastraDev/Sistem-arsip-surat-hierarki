<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // 1. Tampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // 2. Proses Login
    public function login(Request $request)
    {
        // Validasi input
        $data = $request->validate([
            'nip' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $user = User::where('nip', $data['nip'])->first();

        // Fallback hanya untuk akun lama yang belum punya NIP, agar tidak terkunci.
        if (! $user && str_contains($data['nip'], '@')) {
            $user = User::whereNull('nip')->where('email', $data['nip'])->first();
        }

        if ($user && Hash::check($data['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            // Redirect sesuai Role (Penting!)
            // Nanti kita arahkan ke dashboard masing-masing
            return redirect()->intended('dashboard');
        }

        // Jika salah
        return back()->withErrors([
            'nip' => 'NIP atau password salah.',
        ]);
    }

    // 3. Proses Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
