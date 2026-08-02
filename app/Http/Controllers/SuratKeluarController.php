<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use App\Models\LogSuratKeluar;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureLegacyAllowed();

        $user = Auth::user();

        // Load relasi 'pembuat' dan 'posisi' agar efisien
        $query = SuratKeluar::with(['posisi', 'pembuat']);

        // ==========================================================
        // 1. FILTER UMUM (Pencarian & Atribut)
        // ==========================================================

        if ($request->filled('search')) {
            $query->where('perihal', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_surat', $request->tahun);
        }

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal_surat', $request->bulan);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        // ==========================================================
        // 2. FILTER STATUS & HISTORY (Perbaikan Utama Disini)
        // ==========================================================

        if ($request->filled('filter')) {
            switch ($request->filter) {
                // --- Filter Status Aktif ---
                case 'proses':
                    // Untuk Pimpinan: Tampilkan yg ada di meja mereka (Tugas Validasi)
                    if (in_array($user->role, ['kabid', 'kasi'])) {
                        $query->where('posisi_saat_ini', $user->id);
                    } else {
                        // Untuk Staff/Admin: Tampilkan semua yg sedang berjalan
                        $query->whereIn('status_acc', ['pending_kasi', 'pending_kabid']);
                    }
                    break;

                case 'acc':
                    $query->where('status_acc', 'acc');
                    break;

                case 'revisi':
                    $query->where('status_acc', 'revisi');
                    break;

                case 'ditolak':
                    $query->where('status_acc', 'ditolak');
                    break;

                // --- Filter History (Menggunakan Tabel Log) ---
                // Perbaikan: Tidak lagi pakai 'posisi_terakhir_id', tapi pakai whereHas('logs')

                case 'history_acc':
                    $query->whereHas('logs', function ($q) use ($user) {
                        $q->where('from_user_id', $user->id)->where('aksi', 'acc');
                    });
                    break;

                case 'history_revisi':
                    $query->whereHas('logs', function ($q) use ($user) {
                        $q->where('from_user_id', $user->id)
                            ->where('aksi', 'revisi')
                            ->where('catatan_revisi', 'NOT LIKE', '%DITOLAK PERMANEN%');
                    });
                    break;

                case 'history_ditolak':
                    $query->whereHas('logs', function ($q) use ($user) {
                        $q->where('from_user_id', $user->id)
                            ->where(function ($sub) {
                                $sub->where('aksi', 'ditolak')
                                    ->orWhere('catatan_revisi', 'LIKE', '%DITOLAK PERMANEN%');
                            });
                    });
                    break;
            }
        }

        // ==========================================================
        // 3. FILTER HAK AKSES (Role Base)
        // ==========================================================

        // Jika sedang filter History, kita sudah handle di atas (switch case).
        // Jika TIDAK filter history, kita terapkan batasan view default.

        $isHistoryFilter = str_contains($request->filter ?? '', 'history_');

        if (!$isHistoryFilter) {
            if ($user->role == 'staff') {
                // Staff hanya melihat surat buatannya sendiri
                $query->where('pembuat_id', $user->id);
            } elseif (in_array($user->role, ['kabid', 'kasi'])) {
                // Jika tidak ada filter spesifik, Pimpinan melihat:
                // 1. Surat di meja mereka (Tugas)
                // 2. ATAU surat yang mereka buat sendiri (jika ada)
                // 3. ATAU jika filter kosong, tampilkan semua yg relevan (opsional, disini kita batasi)

                // Agar tidak bertabrakan dengan filter 'proses' di atas, kita gunakan logic:
                // Jika filter kosong, tampilkan default view (Tugas & Buatan Sendiri)
                if (!$request->filled('filter')) {
                    $query->where(function ($q) use ($user) {
                        $q->where('posisi_saat_ini', $user->id)
                            ->orWhere('pembuat_id', $user->id);
                    });
                }
            }
        }

        // ==========================================================
        // 4. ORDERING & PAGINATION
        // ==========================================================

        // Ambil list kategori untuk dropdown
        $kategoris = SuratKeluar::select('kategori')
            ->distinct()
            ->whereNotNull('kategori')
            ->orderBy('kategori')
            ->pluck('kategori');

        // Urutkan dan Paginate
        $data = $query->latest()->paginate(15);

        return view('surat-keluar.index', compact('data', 'kategoris'));
    }

    public function create()
    {
        $this->ensureLegacyAllowed();

        return view('surat-keluar.create');
    }

    public function store(Request $request)
    {
        $this->ensureLegacyAllowed();

        $request->validate([
            'file_surat' => 'required|mimes:pdf,doc,docx|max:20480', // Max 20MB
            'kategori' => 'required',
            'perihal' => 'required',
            'tanggal_surat' => 'required|date',
        ]);

        $path = $request->file('file_surat')->store('surat-keluar', 'public');
        $kasi = User::where('id', Auth::user()->parent_id)->first();

        if (!$kasi) {
            return back()->with('error', 'Akun Anda belum memiliki atasan (Kasi). Harap hubungi Admin.');
        }

        $surat = SuratKeluar::create([
            'nomor_surat' => $request->nomor_surat, // Biasanya null saat create
            'kategori' => $request->kategori,
            'tanggal_surat' => $request->tanggal_surat,
            'perihal' => $request->perihal,
            'file_path' => $path,
            'pembuat_id' => Auth::id(),
            'posisi_saat_ini' => $kasi->id,
            'status_acc' => 'pending_kasi',
        ]);

        LogSuratKeluar::create([
            'surat_keluar_id' => $surat->id,
            'from_user_id'    => Auth::id(),
            'to_user_id'      => $kasi->id,
            'aksi'            => 'upload',
            'catatan_revisi'  => 'Draft baru dibuat.'
        ]);

        return redirect()->route('surat-keluar.index')->with('success', 'Draft surat berhasil dikirim ke Kasi!');
    }

    public function show($id)
    {
        $this->ensureLegacyAllowed();

        // Load relasi logs dengan user pengirim/penerima
        $surat = SuratKeluar::with(['logs.pengirim', 'logs.penerima', 'pembuat', 'posisi'])->findOrFail($id);
        $user = Auth::user();

        // Cek Hak Akses
        $isAllowed = false;
        if ($user->role == 'admin') $isAllowed = true;
        else if ($surat->pembuat_id == $user->id) $isAllowed = true;
        else if ($surat->posisi_saat_ini == $user->id) $isAllowed = true;
        else if (in_array($user->role, ['kabid', 'kasi'])) {
            // Pimpinan boleh lihat jika surat pernah lewat di mereka (cek log)
            // Atau sedang ada di meja mereka
            $hasLog = $surat->logs()->where('from_user_id', $user->id)->exists();
            if ($hasLog || $surat->posisi_saat_ini == $user->id) {
                $isAllowed = true;
            }
        }

        if (!$isAllowed) abort(403);

        $catatanRevisi = LogSuratKeluar::where('surat_keluar_id', $id)
            ->where('aksi', 'revisi')
            ->latest()->first();

        return view('surat-keluar.show', compact('surat', 'catatanRevisi'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureLegacyAllowed();

        $surat = SuratKeluar::findOrFail($id);
        $user = Auth::user();

        // --- A. STAFF UPLOAD REVISI ---
        if ($request->hasFile('file_surat')) {
            $request->validate([
                'file_surat' => 'required|mimes:pdf,doc,docx|max:20480',
            ]);

            $path = $request->file('file_surat')->store('surat-keluar', 'public');
            $surat->file_path = $path;

            // Kembalikan ke Kasi (Atasan Langsung)
            $kasi = User::where('id', $surat->pembuat->parent_id)->first();

            if (!$kasi) return back()->with('error', 'Atasan tidak ditemukan.');

            $surat->posisi_saat_ini = $kasi->id;
            $surat->status_acc = 'pending_kasi';
            $surat->save();

            LogSuratKeluar::create([
                'surat_keluar_id' => $surat->id,
                'from_user_id'    => $user->id,
                'to_user_id'      => $kasi->id,
                'aksi'            => 'resubmit',
                'catatan_revisi'  => 'File revisi telah diupload ulang.'
            ]);

            return back()->with('success', 'Revisi berhasil dikirim!');
        }

        // --- B. PIMPINAN VALIDASI ---

        // 1. AKSI: ACC
        if ($request->aksi == 'acc') {
            if ($user->role == 'kasi') {
                $kabid = User::where('role', 'kabid')->first();
                if (!$kabid) return back()->with('error', 'Data Kabid tidak ditemukan.');

                $surat->posisi_saat_ini = $kabid->id;
                $surat->status_acc = 'pending_kabid';
                $surat->save();

                LogSuratKeluar::create([
                    'surat_keluar_id' => $surat->id,
                    'from_user_id'    => $user->id,
                    'to_user_id'      => $kabid->id,
                    'aksi'            => 'acc',
                    'catatan_revisi'  => $request->catatan
                ]);
            } elseif ($user->role == 'kabid') {
                $surat->posisi_saat_ini = null; // Selesai
                $surat->status_acc = 'acc';
                $surat->nomor_surat = '522/' . rand(100, 999) . '/DISHUT/' . date('Y');
                $surat->save();

                LogSuratKeluar::create([
                    'surat_keluar_id' => $surat->id,
                    'from_user_id'    => $user->id,
                    'to_user_id'      => $surat->pembuat_id, // Notif balik ke staff
                    'aksi'            => 'acc',
                    'catatan_revisi'  => 'Surat disetujui dan diterbitkan.'
                ]);
            }
            return back()->with('success', 'Surat berhasil disetujui!');
        }

        // 2. AKSI: REVISI
        elseif ($request->aksi == 'revisi') {
            $surat->status_acc = 'revisi';
            $surat->posisi_saat_ini = $surat->pembuat_id; // Balik ke Staff
            $surat->save();

            LogSuratKeluar::create([
                'surat_keluar_id' => $surat->id,
                'from_user_id'    => $user->id,
                'to_user_id'      => $surat->pembuat_id,
                'aksi'            => 'revisi',
                'catatan_revisi'  => $request->catatan
            ]);

            return back()->with('success', 'Surat dikembalikan untuk revisi.');
        }

        // 3. AKSI: DITOLAK
        elseif ($request->aksi == 'ditolak') {
            $surat->status_acc = 'ditolak';
            $surat->posisi_saat_ini = $surat->pembuat_id; // Balik ke Staff (tapi status mati)
            $surat->save();

            LogSuratKeluar::create([
                'surat_keluar_id' => $surat->id,
                'from_user_id'    => $user->id,
                'to_user_id'      => $surat->pembuat_id,
                'aksi'            => 'ditolak', // Gunakan 'ditolak' agar jelas di log
                'catatan_revisi'  => 'DITOLAK PERMANEN: ' . ($request->catatan ?? 'Tidak memenuhi syarat.')
            ]);

            return back()->with('success', 'Surat telah DITOLAK secara permanen.');
        }
    }

    private function ensureLegacyAllowed(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Modul lama sudah diganti oleh Pengajuan Surat.');
    }
}
