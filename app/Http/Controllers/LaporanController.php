<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LaporanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $query = PengajuanSurat::query();

        if ($user->role === 'staff') {
            $query->where('pemohon_id', $user->id);
        } elseif (in_array($user->role, ['kasi', 'kabid'], true)) {
            $query->where(function ($subQuery) use ($user): void {
                $subQuery->where('posisi_saat_ini', $user->id)
                    ->orWhereHas('riwayat', function ($historyQuery) use ($user): void {
                        $historyQuery->where('actor_id', $user->id);
                    });
            });
        }

        $pengajuanTotal = (clone $query)->count();
        $pengajuanAktif = (clone $query)->whereNotIn('status', ['selesai', 'ditolak'])->count();
        $pengajuanRevisi = (clone $query)->whereIn('status', ['draft', 'ditolak'])->count();
        $pengajuanSelesai = (clone $query)->where('status', 'selesai')->count();
        $pengajuanSiapTtd = $user->role === 'kabid'
            ? PengajuanSurat::where('posisi_saat_ini', $user->id)->where('status', 'disetujui_kabid')->count()
            : 0;

        $pengajuanTerbaru = (clone $query)
            ->with(['jenisSurat', 'pemohon', 'posisi'])
            ->latest()
            ->limit(6)
            ->get();

        $totalUser = User::count();
        $totalJenisSurat = JenisSurat::count();

        return view('dashboard.index', compact(
            'pengajuanTotal',
            'pengajuanAktif',
            'pengajuanRevisi',
            'pengajuanSelesai',
            'pengajuanSiapTtd',
            'pengajuanTerbaru',
            'totalUser',
            'totalJenisSurat'
        ));
    }
}
