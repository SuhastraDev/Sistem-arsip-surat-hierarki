<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\DisposisiSurat;
use App\Models\PengajuanSurat;
use App\Models\User;

class LaporanController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // [PERBAIKAN] Pindahkan ini ke paling ATAS agar tidak error di blok Admin
        $total_user = User::count();

        // Variable Default
        $sm_total = 0;
        $sm_pending = 0;
        $sm_selesai = 0;
        $sk_total = 0;
        $sk_proses = 0;
        $sk_acc = 0;
        $sk_revisi = 0;
        $sk_ditolak = 0;

        // Variable Khusus Admin
        $sm_pending_kabid = 0;
        $sm_pending_kasi = 0;
        $sm_pending_staf = 0;

        if (in_array($user->role, ['kabid', 'kasi'])) {
            $pengajuan_aktif = PengajuanSurat::where('posisi_saat_ini', $user->id)->count();
            $pengajuan_siap_ttd = $user->role === 'kabid'
                ? PengajuanSurat::where('posisi_saat_ini', $user->id)->where('status', 'disetujui_kabid')->count()
                : 0;
            $pengajuan_selesai = PengajuanSurat::where('status', 'selesai')
                ->whereHas('riwayat', fn ($query) => $query->where('actor_id', $user->id))
                ->count();
            $pengajuan_revisi = PengajuanSurat::whereHas('riwayat', function ($query) use ($user): void {
                $query->where('actor_id', $user->id)->whereIn('aksi', ['revisi_kasi', 'revisi_kabid', 'ditolak_kasi', 'ditolak_kabid']);
            })->count();

            return view('dashboard.index', compact(
                'pengajuan_aktif',
                'pengajuan_siap_ttd',
                'pengajuan_selesai',
                'pengajuan_revisi',
                'total_user'
            ));
        }

        // =========================================================
        // SKENARIO 1: ADMIN (ADMIN LIHAT RINCIAN POSISI)
        // =========================================================
        if ($user->role == 'admin') {

            $sm_total = SuratMasuk::count();

            // Hitung Rincian Posisi
            $sm_pending_kabid = SuratMasuk::where('status_akhir', 'Menunggu Disposisi Kabid')->count();
            $sm_pending_kasi  = SuratMasuk::where('status_akhir', 'Disposisi di Meja Kasi')->count();
            $sm_pending_staf  = SuratMasuk::where('status_akhir', 'Disposisi di Meja Staf')->count();

            // Total Pending (Jumlah dari ketiga posisi di atas)
            $sm_pending = $sm_pending_kabid + $sm_pending_kasi + $sm_pending_staf;

            // Sisanya dianggap Selesai/Arsip
            $sm_selesai = $sm_total - $sm_pending;

            // Surat Keluar Global
            $sk_total   = SuratKeluar::count();
            $sk_proses  = SuratKeluar::whereIn('status_acc', ['pending_kasi', 'pending_kabid'])->count();
            $sk_acc     = SuratKeluar::where('status_acc', 'acc')->count();
            $sk_revisi  = SuratKeluar::where('status_acc', 'revisi')->count();
            $sk_ditolak = SuratKeluar::where('status_acc', 'ditolak')->count();

            // Return Khusus Admin (Include variabel rincian)
            return view('dashboard.index', compact(
                'sm_total',
                'sm_pending',
                'sm_selesai',
                'sm_pending_kabid',
                'sm_pending_kasi',
                'sm_pending_staf',
                'sk_total',
                'sk_proses',
                'sk_acc',
                'sk_revisi',
                'sk_ditolak',
                'total_user'
            ));
        }

        // =========================================================
        // SKENARIO 2: STAFF (FOKUS INBOX & SURAT SENDIRI)
        // =========================================================
        elseif ($user->role == 'staff') {

            // Surat Masuk: Inbox Disposisi Saya
            $sm_total   = DisposisiSurat::where('penerima_id', $user->id)->count();
            $sm_pending = DisposisiSurat::where('penerima_id', $user->id)->where('is_read', 0)->count();
            $sm_selesai = DisposisiSurat::where('penerima_id', $user->id)->where('is_read', 1)->count();

            // Surat Keluar: Hanya Buatan Saya
            $query_sk   = SuratKeluar::where('pembuat_id', $user->id);

            $sk_total   = (clone $query_sk)->count();
            $sk_proses  = (clone $query_sk)->whereIn('status_acc', ['pending_kasi', 'pending_kabid'])->count();
            $sk_acc     = (clone $query_sk)->where('status_acc', 'acc')->count();
            $sk_revisi  = (clone $query_sk)->where('status_acc', 'revisi')->count();
            $sk_ditolak = (clone $query_sk)->where('status_acc', 'ditolak')->count();
        }

        // =========================================================
        // SKENARIO 3: PIMPINAN (KABID & KASI)
        // =========================================================
        else {
            // --- SURAT MASUK ---
            $sm_total   = \App\Models\DisposisiSurat::where('penerima_id', $user->id)->count();
            $sm_pending = \App\Models\DisposisiSurat::where('penerima_id', $user->id)->where('is_read', 0)->count();
            $sm_selesai = \App\Models\DisposisiSurat::where('penerima_id', $user->id)->where('is_read', 1)->count();

            // --- SURAT KELUAR: STATISTIK KINERJA SAYA ---

            // 1. Tugas Aktif (Yang harus dikerjakan sekarang)
            $sk_proses  = \App\Models\SuratKeluar::where('posisi_saat_ini', $user->id)->count();

            // 2. Yang SUDAH saya proses (Ambil dari Log)
            // Hitung log di mana SAYA adalah pengirim (aktor)

            // A. Saya ACC
            $sk_acc = \App\Models\LogSuratKeluar::where('from_user_id', $user->id)
                ->where('aksi', 'acc')->count();

            // B. Saya Revisi (Aksi 'revisi' tapi catatan BUKAN tolak permanen)
            $sk_revisi = \App\Models\LogSuratKeluar::where('from_user_id', $user->id)
                ->where('aksi', 'revisi')
                ->where('catatan_revisi', 'NOT LIKE', '%DITOLAK PERMANEN%')
                ->count();

            // C. Saya Tolak (Aksi 'ditolak' ATAU aksi 'revisi' yang ada kata DITOLAK)
            // (Mengantisipasi jika di database tersimpan sebagai 'revisi' tapi isinya tolak)
            $sk_ditolak = \App\Models\LogSuratKeluar::where('from_user_id', $user->id)
                ->where(function ($q) {
                    $q->where('aksi', 'ditolak')
                        ->orWhere('catatan_revisi', 'LIKE', '%DITOLAK PERMANEN%');
                })->count();

            // Total yang sudah saya kerjakan (ACC + Revisi + Tolak)
            $sk_total = $sk_acc + $sk_revisi + $sk_ditolak;
        }

        // Return Umum (Staff & Pimpinan)
        return view('dashboard.index', compact(
            'sm_total',
            'sm_pending',
            'sm_selesai',
            'sk_total',
            'sk_proses',
            'sk_acc',
            'sk_revisi',
            'sk_ditolak',
            'total_user'
        ));
    }
}
