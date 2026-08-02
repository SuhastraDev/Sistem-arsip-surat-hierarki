@extends('layouts.main')

@section('title', 'Dashboard Pengajuan')

@section('content')
@php
    $role = Auth::user()->role;
    $hero = match($role) {
        'admin' => [
            'kicker' => 'Ruang Kendali',
            'title' => 'Monitoring Pengajuan Surat',
            'copy' => 'Pantau pengajuan baru, kelola jenis surat, dan atur pengguna dari satu dashboard.',
            'icon' => 'fas fa-compass-drafting',
        ],
        'staff' => [
            'kicker' => 'Meja Staff',
            'title' => 'Pengajuan Saya',
            'copy' => 'Buat pengajuan, ikuti statusnya, dan ambil dokumen final setelah ditandatangani Kabid.',
            'icon' => 'fas fa-file-pen',
        ],
        'kasi' => [
            'kicker' => 'Meja Pemeriksaan',
            'title' => 'Review Pengajuan Staff',
            'copy' => 'Periksa permohonan yang masuk, teruskan ke Kabid, atau kembalikan untuk revisi.',
            'icon' => 'fas fa-user-check',
        ],
        default => [
            'kicker' => 'Meja Tanda Tangan',
            'title' => 'Approval Kabid',
            'copy' => 'Selesaikan pemeriksaan akhir, tanda tangani dokumen, lalu kirim hasil final ke Staff.',
            'icon' => 'fas fa-signature',
        ],
    };
@endphp

<div class="dashboard-hero mb-4">
    <div class="hero-mark"><i class="{{ $hero['icon'] }}"></i></div>
    <div>
        <div class="section-kicker">{{ $hero['kicker'] }}</div>
        <h3 class="mb-1">{{ $hero['title'] }}</h3>
        <p class="text-muted mb-0">{{ $hero['copy'] }}</p>
    </div>
    <div class="hero-actions">
        @if($role === 'staff')
        <a href="{{ route('pengajuan-surat.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>Buat Pengajuan
        </a>
        @endif
        @if($role === 'admin')
        <a href="{{ route('jenis-surat.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-layer-group me-1"></i>Jenis Surat
        </a>
        <a href="{{ route('users.index') }}" class="btn btn-primary">
            <i class="fas fa-users-cog me-1"></i>Pengguna
        </a>
        @else
        <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-primary">
            <i class="fas fa-list-check me-1"></i>Buka Pengajuan
        </a>
        @endif
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index') }}" class="text-decoration-none">
            <div class="metric-tile primary">
                <span>Total</span>
                <strong>{{ $pengajuanTotal }}</strong>
                <small>Pengajuan terlihat</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index') }}" class="text-decoration-none">
            <div class="metric-tile gold">
                <span>Aktif</span>
                <strong>{{ $pengajuanAktif }}</strong>
                <small>Belum selesai</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index', ['status' => 'selesai']) }}" class="text-decoration-none">
            <div class="metric-tile success">
                <span>Selesai</span>
                <strong>{{ $pengajuanSelesai }}</strong>
                <small>Kembali ke Staff</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        @if($role === 'kabid')
        <a href="{{ route('pengajuan-surat.index', ['status' => 'disetujui_kabid']) }}" class="text-decoration-none">
            <div class="metric-tile ink">
                <span>Siap TTD</span>
                <strong>{{ $pengajuanSiapTtd }}</strong>
                <small>Butuh tanda tangan</small>
            </div>
        </a>
        @else
        <a href="{{ route('pengajuan-surat.index', ['status' => 'draft']) }}" class="text-decoration-none">
            <div class="metric-tile ink">
                <span>Revisi/Tolak</span>
                <strong>{{ $pengajuanRevisi }}</strong>
                <small>Perlu perhatian</small>
            </div>
        </a>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dashboard-panel h-100">
            <div class="dashboard-panel-header">
                <div>
                    <h6 class="mb-1 fw-bold">Pengajuan Terbaru</h6>
                    <small class="text-muted">Daftar ini mengikuti akses role yang sedang login.</small>
                </div>
                <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-right me-1"></i>Lihat Semua
                </a>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Jenis</th>
                            <th>Pemohon</th>
                            <th>Status</th>
                            <th>Posisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengajuanTerbaru as $pengajuan)
                        <tr>
                            <td><code>{{ $pengajuan->nomor_pengajuan }}</code></td>
                            <td>{{ $pengajuan->jenisSurat->nama }}</td>
                            <td>{{ $pengajuan->pemohon->name }}</td>
                            <td><span class="badge bg-secondary">{{ $pengajuan->status_label }}</span></td>
                            <td>{{ $pengajuan->tahap_label }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-folder-open fa-2x text-muted mb-3"></i>
                                <h6 class="fw-bold mb-1">Belum ada pengajuan</h6>
                                <p class="text-muted mb-0 small">Data akan muncul setelah Staff membuat pengajuan surat.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="dashboard-panel h-100">
            <div class="dashboard-panel-header">
                <div>
                    <h6 class="mb-1 fw-bold">Alur Aktif</h6>
                    <small class="text-muted">Ringkas tanpa modul lama.</small>
                </div>
            </div>
            <div class="flow-list">
                <div><i class="fas fa-user-pen"></i><strong>Staff</strong><span>Membuat pengajuan dari template resmi.</span></div>
                <div><i class="fas fa-user-check"></i><strong>Kasi</strong><span>Memeriksa dan meneruskan ke Kabid.</span></div>
                <div><i class="fas fa-signature"></i><strong>Kabid</strong><span>Menandatangani dan mengirim final ke Staff.</span></div>
                <div><i class="fas fa-qrcode"></i><strong>Verifikasi</strong><span>Dokumen final dicek lewat QR/kode publik.</span></div>
            </div>
            @if($role === 'admin')
            <div class="admin-summary">
                <div><span>Pengguna</span><strong>{{ $totalUser }}</strong></div>
                <div><span>Jenis Surat</span><strong>{{ $totalJenisSurat }}</strong></div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .dashboard-hero,
    .dashboard-panel,
    .metric-tile {
        background: rgba(255, 255, 255, .94);
        border: 1px solid #d9ded6;
        border-radius: 8px;
        box-shadow: 0 14px 32px rgba(16, 32, 51, .08);
    }

    .dashboard-hero {
        align-items: center;
        display: grid;
        gap: 18px;
        grid-template-columns: auto 1fr auto;
        padding: 24px;
    }

    .hero-mark {
        align-items: center;
        background: #eef7f2;
        border: 1px solid #cfe7dc;
        border-radius: 8px;
        color: #0f766e;
        display: flex;
        height: 58px;
        justify-content: center;
        width: 58px;
    }

    .hero-mark i {
        font-size: 1.45rem;
    }

    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .metric-tile {
        border-left: 5px solid #0f766e;
        color: #102033;
        min-height: 126px;
        padding: 18px;
    }

    .metric-tile.gold {
        border-left-color: #d8a030;
    }

    .metric-tile.success {
        border-left-color: #16a34a;
    }

    .metric-tile.ink {
        border-left-color: #1d4d7a;
    }

    .metric-tile span,
    .metric-tile small {
        color: #647083;
        display: block;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .metric-tile strong {
        display: block;
        font-size: 2.25rem;
        line-height: 1.1;
        margin: 8px 0;
    }

    .dashboard-panel-header {
        align-items: center;
        background: linear-gradient(180deg, #fffdf8 0%, #f5f2ea 100%);
        border-bottom: 1px solid #d9ded6;
        display: flex;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .flow-list {
        display: grid;
        gap: 10px;
        padding: 16px;
    }

    .flow-list div {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: grid;
        gap: 2px 12px;
        grid-template-columns: 34px 1fr;
        padding: 12px;
    }

    .flow-list i {
        align-items: center;
        background: #eef7f2;
        border-radius: 8px;
        color: #0f766e;
        display: flex;
        grid-row: span 2;
        justify-content: center;
    }

    .flow-list span {
        color: #647083;
        font-size: .82rem;
    }

    .admin-summary {
        border-top: 1px solid #d9ded6;
        display: grid;
        gap: 10px;
        grid-template-columns: 1fr 1fr;
        padding: 16px;
    }

    .admin-summary div {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
    }

    .admin-summary span {
        color: #647083;
        display: block;
        font-size: .76rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .admin-summary strong {
        color: #102033;
        font-size: 1.5rem;
    }

    @media (max-width: 992px) {
        .dashboard-hero {
            grid-template-columns: 1fr;
        }

        .hero-actions {
            justify-content: flex-start;
        }
    }
</style>
@endsection
