<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\DisposisiSurat;
use Illuminate\Support\Facades\Auth;

class SuratMasukController extends Controller
{
    // 1. TAMPILKAN DAFTAR SURAT (Untuk Admin & Pimpinan melihat)
    public function index(Request $request)
    {
        $this->ensureLegacyAllowed();

        $query = SuratMasuk::latest();

        // --- FILTER DARI DASHBOARD (ADMIN) ---
        if ($request->filter == 'pending_kabid') {
            $query->where('status_akhir', 'Menunggu Disposisi Kabid');
        } elseif ($request->filter == 'pending_kasi') {
            $query->where('status_akhir', 'Disposisi di Meja Kasi');
        } elseif ($request->filter == 'pending_staf') {
            $query->where('status_akhir', 'Disposisi di Meja Staf');
        } elseif ($request->filter == 'selesai') {
            $query->whereNotIn('status_akhir', [
                'Menunggu Disposisi Kabid',
                'Disposisi di Meja Kasi',
                'Disposisi di Meja Staf'
            ]);
        }

        // --- FILTER PENCARIAN ---
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('perihal', 'like', '%' . $search . '%')
                    ->orWhere('nomor_surat', 'like', '%' . $search . '%')
                    ->orWhere('pengirim', 'like', '%' . $search . '%');
            });
        }

        // --- FILTER TAHUN ---
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('tanggal_surat', $request->year);
        }

        // --- FILTER BULAN ---
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('tanggal_surat', $request->month);
        }

        // --- FILTER STATUS ---
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'menunggu_kabid') {
                $query->where('status_akhir', 'Menunggu Disposisi Kabid');
            } elseif ($request->status == 'di_kasi') {
                $query->where('status_akhir', 'Disposisi di Meja Kasi');
            } elseif ($request->status == 'di_staf') {
                $query->where('status_akhir', 'Disposisi di Meja Staf');
            }
        }

        $surat = $query->get();
        return view('surat-masuk.index', compact('surat'));
    }

    // 2. TAMPILKAN FORM TAMBAH (Hanya Admin)
    public function create()
    {
        $this->ensureLegacyAllowed();

        return view('surat-masuk.create');
    }

    public function show($id)
    {
        $this->ensureLegacyAllowed();

        // Ambil surat beserta seluruh sejarah disposisinya
        $surat = SuratMasuk::with(['disposisi.pengirim', 'disposisi.penerima'])->findOrFail($id);

        return view('surat-masuk.show', compact('surat'));
    }

    public function store(Request $request)
    {
        $this->ensureLegacyAllowed();

        // 1. Validasi
        $request->validate([
            'nomor_surat' => 'required|unique:surat_masuk,nomor_surat',
            'kategori' => 'required',
            'tanggal_surat' => 'required|date',
            'pengirim' => 'required',
            'perihal' => 'required',
            'file_surat' => 'required|mimes:pdf,doc,docx|max:2048',
        ]);

        // 2. Upload
        $filePath = $request->file('file_surat')->store('surat-masuk', 'public');

        // SIMPAN SURAT
        $surat = SuratMasuk::create([
            'nomor_surat' => $request->nomor_surat,
            'kategori' => $request->kategori,
            'tanggal_surat' => $request->tanggal_surat,
            'pengirim' => $request->pengirim,
            'perihal' => $request->perihal,
            'file_path' => $filePath,
            'status_akhir' => 'Menunggu Disposisi Kabid',
        ]);

        // Buat Disposisi Awal ke KABID (Otomatis)
        $kabid = User::where('role', 'kabid')->first();
        if ($kabid) {
            DisposisiSurat::create([
                'surat_masuk_id' => $surat->id,
                'pengirim_id' => Auth::id(),
                'penerima_id' => $kabid->id,
                'status' => 'pending',
                'is_read' => 0,
                'instruksi' => 'Mohon ditindaklanjuti',
            ]);
        }

        return redirect()->route('surat-masuk.index')->with('success', 'Surat Masuk berhasil ditambahkan dan dikirim ke Kabid!');
    }

    private function ensureLegacyAllowed(): void
    {
        abort_unless(Auth::user()->role === 'admin', 403, 'Modul lama sudah diganti oleh Pengajuan Surat.');
    }
}
