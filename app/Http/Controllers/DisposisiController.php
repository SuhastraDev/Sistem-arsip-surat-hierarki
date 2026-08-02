<?php

namespace App\Http\Controllers;

use App\Models\DisposisiSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    // 1. INBOX (Kotak Masuk Disposisi Saya)
    public function index(Request $request)
    {
        $this->ensureLegacyAllowed();

        // Ambil disposisi KHUSUS untuk user yang login
        $query = \App\Models\DisposisiSurat::where('penerima_id', Auth::id())->latest();

        // --- LOGIKA FILTER DARI DASHBOARD ---
        if ($request->filter == 'unread') {
            $query->where('is_read', 0); // Hanya yang belum dibaca
        } elseif ($request->filter == 'read') {
            $query->where('is_read', 1); // Hanya yang sudah dibaca
        }

        // ✅ PERBAIKAN: EAGER LOADING untuk menghindari N+1 Problem
        // Load relasi surat dan pengirim sekaligus
        $inbox = $query->with(['surat', 'pengirim'])->get();

        // Hitung juga bawahan (jika user adalah pimpinan) untuk fitur kirim disposisi
        $user = Auth::user();
        $bawahan = collect();
        if ($user->role == 'kabid') {
            $bawahan = \App\Models\User::where('role', 'kasi')->get();
        } elseif ($user->role == 'kasi') {
            $bawahan = \App\Models\User::where('role', 'staff')->get();
        }

        return view('disposisi.index', compact('inbox', 'bawahan'));
    }

    // 2. BACA DETAIL & BUKA KUNCI TOMBOL (Mark as Read)
    public function show($id)
    {
        $this->ensureLegacyAllowed();

        $disposisi = DisposisiSurat::findOrFail($id);

        // Pastikan yang buka adalah penerima yang sah
        if ($disposisi->penerima_id != Auth::id()) {
            abort(403, 'Anda tidak berhak melihat surat ini.');
        }

        // LOGIC "WAJIB BACA": 
        // Jika status belum dibaca, ubah jadi dibaca sekarang.
        if ($disposisi->is_read == 0) {
            $disposisi->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            // ✅ PERBAIKAN: Jika penerima adalah STAFF (tidak punya bawahan), 
            // maka status surat menjadi SELESAI
            $user = Auth::user();
            if ($user->role == 'staff') {
                $suratMasuk = $disposisi->surat;
                $suratMasuk->update([
                    'status_akhir' => 'Selesai - Dibaca oleh Staf'
                ]);
            }
        }

        // Ambil daftar bawahan untuk opsi tujuan disposisi selanjutnya
        $bawahan = Auth::user()->bawahan;

        return view('disposisi.show', compact('disposisi', 'bawahan'));
    }

    // 3. KIRIM DISPOSISI KE BAWAHAN
    public function update(Request $request, $id)
    {
        $this->ensureLegacyAllowed();

        // 1. Validasi input harus Array
        $request->validate([
            'tujuan_id' => 'required|array', // Wajib array
            'tujuan_id.*' => 'exists:users,id', // Setiap item harus ID user yang valid
            'instruksi' => 'required|string',
        ]);

        // Ambil disposisi saat ini (yang sedang dibuka oleh Pimpinan)
        $disposisiSaatIni = DisposisiSurat::findOrFail($id);
        // Ambil Data Surat Masuk Induknya
        $suratMasuk = \App\Models\SuratMasuk::findOrFail($disposisiSaatIni->surat_masuk_id);

        // VARIABEL UNTUK MENENTUKAN POSISI BARU
        $posisiBaru = '';

        // LOOPING PEMBUATAN DISPOSISI BARU
        foreach ($request->tujuan_id as $penerima_id) {

            DisposisiSurat::create([
                'surat_masuk_id' => $disposisiSaatIni->surat_masuk_id,
                'pengirim_id'    => Auth::id(),
                'penerima_id'    => $penerima_id,
                'instruksi'      => $request->instruksi,
                'status'         => 'pending',
                'is_read'        => 0
            ]);

            // Cek Role Penerima untuk Update Status Induk
            $penerima = \App\Models\User::find($penerima_id);
            if ($penerima) {
                if ($penerima->role == 'kasi') {
                    $posisiBaru = 'Disposisi di Meja Kasi';
                } elseif ($penerima->role == 'staff') {
                    $posisiBaru = 'Disposisi di Meja Staf';
                }
            }
        }

        // UPDATE STATUS DISPOSISI SAYA JADI SELESAI
        $disposisiSaatIni->update(['status' => 'selesai']);

        // UPDATE STATUS DI TABEL SURAT MASUK
        if ($posisiBaru != '') {
            $suratMasuk->update(['status_akhir' => $posisiBaru]);
        }

        $jumlahPenerima = count($request->tujuan_id);
        return redirect()->route('disposisi.index')
            ->with('success', "Disposisi diteruskan ke $jumlahPenerima orang. Posisi surat diperbarui.");
    }

    private function ensureLegacyAllowed(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Modul lama sudah diganti oleh Pengajuan Surat.');
    }
}
