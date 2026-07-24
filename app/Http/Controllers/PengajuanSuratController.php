<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengajuanSuratController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PengajuanSurat::with(['jenisSurat', 'pemohon', 'posisi']);

        if ($request->filled('search')) {
            $query->where('perihal', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('jenis_surat_id')) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_pengajuan', $request->tahun);
        }

        if ($user->role === 'staff') {
            $query->where('pemohon_id', $user->id);
        } elseif (in_array($user->role, ['kasi', 'kabid'])) {
            $query->where(function ($subQuery) use ($user): void {
                $subQuery->where('posisi_saat_ini', $user->id)
                    ->orWhere('pemohon_id', $user->id);
            });
        }

        $pengajuanSurats = $query->latest()->paginate(15);
        $jenisSurats = JenisSurat::orderBy('nama')->get();
        $statusOptions = PengajuanSurat::STATUS;

        return view('pengajuan-surat.index', compact('pengajuanSurats', 'jenisSurats', 'statusOptions'));
    }

    public function create()
    {
        $jenisSurats = JenisSurat::aktif()->orderBy('nama')->get();

        return view('pengajuan-surat.create', compact('jenisSurats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
            'tanggal_pengajuan' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:1000'],
        ]);

        $posisiAwal = $this->resolvePosisiAwal();

        if (! $posisiAwal) {
            return back()
                ->withInput()
                ->with('error', 'Akun Anda belum memiliki jalur atasan untuk pengajuan. Hubungi Admin.');
        }

        PengajuanSurat::create([
            'jenis_surat_id' => $data['jenis_surat_id'],
            'pemohon_id' => Auth::id(),
            'nomor_pengajuan' => $this->generateNomorPengajuan(),
            'tanggal_pengajuan' => $data['tanggal_pengajuan'],
            'perihal' => $data['perihal'],
            'status' => 'diajukan',
            'posisi_saat_ini' => $posisiAwal->id,
            'metadata' => [
                'fase' => 'fase_1',
                'catatan' => 'Struktur awal pengajuan. Form persyaratan detail dibuat pada Fase 2.',
            ],
        ]);

        return redirect()
            ->route('pengajuan-surat.index')
            ->with('success', 'Pengajuan surat berhasil dibuat dan masuk ke antrean pemeriksaan.');
    }

    public function show(PengajuanSurat $pengajuanSurat)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'posisi']);
        $user = Auth::user();

        $isAllowed = $user->role === 'admin'
            || $pengajuanSurat->pemohon_id === $user->id
            || $pengajuanSurat->posisi_saat_ini === $user->id;

        abort_unless($isAllowed, 403);

        return view('pengajuan-surat.show', compact('pengajuanSurat'));
    }

    private function resolvePosisiAwal(): ?User
    {
        $user = Auth::user();

        if ($user->role === 'staff' && $user->parent_id) {
            return User::find($user->parent_id);
        }

        if ($user->role === 'kasi') {
            return User::where('role', 'kabid')->first();
        }

        if ($user->role === 'admin') {
            return User::where('role', 'kasi')->first() ?? User::where('role', 'kabid')->first();
        }

        return null;
    }

    private function generateNomorPengajuan(): string
    {
        $prefix = 'PGJ-'.now()->format('Ymd');
        $countToday = PengajuanSurat::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
    }
}
