@extends('layouts.main')

@section('title', 'Detail Akun Pegawai')

@section('content')
<div class="account-detail-shell">
    <div class="detail-hero mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-light border btn-sm">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div class="detail-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <div>
                    <div class="section-kicker">Detail Akun Pegawai</div>
                    <h4 class="mb-1 fw-bold">{{ $user->name }}</h4>
                    <div class="text-muted">{{ $user->jabatan }}</div>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <span class="role-pill role-{{ $user->role }}">{{ strtoupper($user->role) }}</span>
                <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus akun pegawai ini? Data surat terkait mungkin akan kehilangan referensi.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash-alt me-1"></i>Hapus Akun
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="detail-card h-100">
                <div class="detail-card-header">
                    <strong><i class="fas fa-id-card me-2 text-primary"></i>Identitas Akun</strong>
                </div>
                <div class="detail-list">
                    <div>
                        <span>Nama Lengkap</span>
                        <strong>{{ $user->name }}</strong>
                    </div>
                    <div>
                        <span>NIP</span>
                        <strong>{{ $user->nip ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Email Login</span>
                        <strong>{{ $user->email }}</strong>
                    </div>
                    <div>
                        <span>Jabatan</span>
                        <strong>{{ $user->jabatan ?: '-' }}</strong>
                    </div>
                    <div>
                        <span>Dibuat Pada</span>
                        <strong>{{ $user->created_at?->format('d/m/Y H:i') ?: '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="detail-card h-100">
                <div class="detail-card-header">
                    <strong><i class="fas fa-sitemap me-2 text-primary"></i>Hierarki</strong>
                </div>
                <div class="hierarchy-box">
                    <div class="hierarchy-node muted">
                        <span>Atasan Langsung</span>
                        @if($user->atasan)
                        <strong>{{ $user->atasan->name }}</strong>
                        <small>{{ $user->atasan->jabatan }}</small>
                        @else
                        <strong>-</strong>
                        <small>Tidak memiliki atasan langsung</small>
                        @endif
                    </div>
                    <div class="hierarchy-line"></div>
                    <div class="hierarchy-node active">
                        <span>Akun Ini</span>
                        <strong>{{ $user->name }}</strong>
                        <small>{{ ucfirst($user->role) }}</small>
                    </div>
                </div>
                <div class="subordinate-list">
                    <span>Bawahan Langsung</span>
                    @forelse($user->bawahan as $bawahan)
                    <div class="subordinate-item">
                        <i class="fas fa-user"></i>
                        <div>
                            <strong>{{ $bawahan->name }}</strong>
                            <small>{{ $bawahan->jabatan }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-muted small mt-2">Belum ada bawahan langsung.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="detail-card h-100">
                <div class="detail-card-header">
                    <strong><i class="fas fa-chart-simple me-2 text-primary"></i>Ringkasan Aktivitas</strong>
                </div>
                <div class="stats-grid">
                    <div class="stat-box">
                        <span>Pengajuan Dibuat</span>
                        <strong>{{ $stats['pengajuan_dibuat'] }}</strong>
                    </div>
                    <div class="stat-box">
                        <span>Sedang di Meja Ini</span>
                        <strong>{{ $stats['pengajuan_di_meja'] }}</strong>
                    </div>
                    <div class="stat-box">
                        <span>Bawahan</span>
                        <strong>{{ $stats['bawahan'] }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="detail-card mt-3">
        <div class="detail-card-header d-flex justify-content-between align-items-center">
            <strong><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Pengajuan Terbaru</strong>
            <small class="text-muted">Maksimal 5 data terakhir sebagai pemohon</small>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nomor</th>
                        <th>Jenis</th>
                        <th>Perihal</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentPengajuan as $pengajuan)
                    <tr>
                        <td><code>{{ $pengajuan->nomor_pengajuan }}</code></td>
                        <td>{{ $pengajuan->jenisSurat->nama }}</td>
                        <td>{{ Str::limit($pengajuan->perihal, 52) }}</td>
                        <td><span class="badge bg-secondary">{{ $pengajuan->status_label }}</span></td>
                        <td>{{ $pengajuan->tanggal_pengajuan->format('d/m/Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('pengajuan-surat.show', $pengajuan) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>Lihat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Belum ada pengajuan sebagai pemohon.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .account-detail-shell {
        color: #102033;
    }

    .detail-hero,
    .detail-card {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .detail-hero {
        border-left: 5px solid #0f766e;
        padding: 18px;
    }

    .section-kicker {
        color: #0f766e;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .detail-avatar {
        align-items: center;
        background: linear-gradient(135deg, #0f766e, #004085);
        border-radius: 8px;
        color: #fff;
        display: flex;
        font-size: 1.35rem;
        font-weight: 900;
        height: 56px;
        justify-content: center;
        width: 56px;
    }

    .role-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: .74rem;
        font-weight: 900;
        letter-spacing: .08em;
        padding: 8px 12px;
    }

    .role-kabid {
        background: #fee2e2;
        color: #991b1b;
    }

    .role-kasi {
        background: #fef3c7;
        color: #92400e;
    }

    .role-staff {
        background: #dcfce7;
        color: #166534;
    }

    .detail-card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e7edf3;
        padding: 14px 16px;
    }

    .detail-list {
        display: grid;
    }

    .detail-list div {
        border-bottom: 1px solid #edf2f7;
        padding: 13px 16px;
    }

    .detail-list div:last-child {
        border-bottom: 0;
    }

    .detail-list span,
    .subordinate-list > span,
    .stat-box span,
    .hierarchy-node span {
        color: #64748b;
        display: block;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .detail-list strong,
    .hierarchy-node strong,
    .subordinate-item strong {
        color: #0f172a;
        display: block;
        font-size: .92rem;
    }

    .hierarchy-box {
        padding: 16px;
    }

    .hierarchy-node {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
    }

    .hierarchy-node.active {
        background: #ecfdf5;
        border-color: #99f6e4;
    }

    .hierarchy-node.muted {
        background: #f8fafc;
    }

    .hierarchy-node small,
    .subordinate-item small {
        color: #64748b;
        display: block;
        font-size: .78rem;
        margin-top: 2px;
    }

    .hierarchy-line {
        background: #cbd5e1;
        height: 18px;
        margin-left: 18px;
        width: 2px;
    }

    .subordinate-list {
        border-top: 1px solid #edf2f7;
        padding: 16px;
    }

    .subordinate-item {
        align-items: center;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: grid;
        gap: 10px;
        grid-template-columns: 34px 1fr;
        margin-top: 8px;
        padding: 10px;
    }

    .subordinate-item i {
        align-items: center;
        background: #eff6ff;
        border-radius: 8px;
        color: #004085;
        display: flex;
        height: 34px;
        justify-content: center;
        width: 34px;
    }

    .stats-grid {
        display: grid;
        gap: 10px;
        padding: 16px;
    }

    .stat-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
    }

    .stat-box strong {
        color: #0f766e;
        display: block;
        font-size: 1.7rem;
        line-height: 1;
    }

    .table thead th {
        background: #f4f7fb;
        color: #334155;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .04em;
        padding: 12px;
        text-transform: uppercase;
    }

    .table tbody td {
        padding: 12px;
    }
</style>
@endsection
