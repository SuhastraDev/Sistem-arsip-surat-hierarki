@extends('layouts.main')

@section('title', 'Dashboard & Statistik')

@section('content')

@if(in_array(Auth::user()->role, ['kabid', 'kasi']))
<div class="request-overview mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end">
        <div>
            <div class="section-kicker">Meja Approval</div>
            <h4 class="mb-1 fw-bold">Pengajuan Surat</h4>
            <p class="text-muted mb-0">Fokus halaman ini hanya pengajuan surat baru: periksa, setujui, tanda tangani, lalu kirim hasil final ke Staff.</p>
        </div>
        <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-primary">
            <i class="fas fa-file-signature me-1"></i>Buka Pengajuan
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index') }}" class="text-decoration-none">
            <div class="approval-metric primary">
                <span>Antrean aktif</span>
                <strong>{{ $pengajuan_aktif }}</strong>
                <small>Menunggu aksi Anda</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index', ['status' => 'disetujui_kabid']) }}" class="text-decoration-none">
            <div class="approval-metric gold">
                <span>Siap TTD</span>
                <strong>{{ $pengajuan_siap_ttd }}</strong>
                <small>Khusus Kabid</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index', ['status' => 'selesai']) }}" class="text-decoration-none">
            <div class="approval-metric success">
                <span>Selesai</span>
                <strong>{{ $pengajuan_selesai }}</strong>
                <small>Sudah kembali ke Staff</small>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('pengajuan-surat.index') }}" class="text-decoration-none">
            <div class="approval-metric muted">
                <span>Revisi/Tolak</span>
                <strong>{{ $pengajuan_revisi }}</strong>
                <small>Keputusan Anda</small>
            </div>
        </a>
    </div>
</div>

<div class="detail-panel mt-3">
    <div class="detail-panel-header">
        <strong><i class="fas fa-route me-2 text-primary"></i>Alur yang Dipakai</strong>
    </div>
    <div class="p-3">
        <div class="approval-guide">
            <div><i class="fas fa-user"></i><strong>Staff</strong><span>Mengajukan surat dan menerima hasil final.</span></div>
            <div><i class="fas fa-user-check"></i><strong>Kasi</strong><span>Memeriksa, menyetujui, revisi, atau menolak.</span></div>
            <div><i class="fas fa-user-tie"></i><strong>Kabid</strong><span>Memeriksa akhir dan menandatangani dokumen.</span></div>
            <div><i class="fas fa-qrcode"></i><strong>Final</strong><span>PDF/DOCX berisi QR/kode verifikasi dan kembali ke Staff.</span></div>
        </div>
    </div>
</div>
@else

<!-- Welcome Header -->
<div class="mb-3">
    <div class="d-flex justify-content-between align-items-center">
        @if(request('filter'))
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-sync-alt me-1"></i>
            Reset Filter
        </a>
        @endif
    </div>
</div>

<!-- Quick Status Alert -->
<div class="alert border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #004085 0%, #002752 100%);">
    <div class="d-flex align-items-center text-white">
        <div class="flex-shrink-0 me-3">
            <i class="fas fa-bell fa-lg"></i>
        </div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-1">Status Sistem Hari Ini</h6>
            <p class="mb-0 small opacity-75">
                @if(Auth::user()->role == 'staff')
                Anda memiliki <strong>{{ $sm_pending }}</strong> disposisi baru yang belum dibaca,
                dan <strong>{{ $sk_revisi }}</strong> surat keluar yang perlu direvisi.
                @elseif(in_array(Auth::user()->role, ['kabid', 'kasi']))
                Anda memiliki <strong>{{ $sk_proses }}</strong> surat keluar yang menunggu validasi/tanda tangan Anda saat ini.
                @else
                Sistem berjalan normal dengan total <strong>{{ $total_user }}</strong> pengguna aktif.
                @endif
            </p>
        </div>
    </div>
</div>

<div class="row g-3">

    <!-- Left Column: Surat Masuk / Disposisi -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-envelope-open-text text-primary me-2"></i>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                            @if(Auth::user()->role == 'admin')
                            Monitoring Surat Masuk
                            @elseif(Auth::user()->role == 'staff')
                            Kotak Masuk Saya
                            @else
                            Disposisi Masuk
                            @endif
                        </h6>
                        <small class="text-muted" style="font-size: 0.7rem;">Real-time tracking</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                <!-- Main Counter -->
                <a href="{{ Auth::user()->role == 'staff' || in_array(Auth::user()->role, ['kabid', 'kasi']) ? route('disposisi.index') : route('surat-masuk.index') }}"
                    class="text-decoration-none">
                    <div class="card border-0 shadow-sm mb-3 card-hover" style="background: linear-gradient(135deg, #004085 0%, #002752 100%);">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold mb-1" style="font-size: 0.7rem;">TOTAL PESAN MASUK</div>
                                    <h2 class="fw-bold text-white mb-0">{{ $sm_total }}</h2>
                                </div>
                                <div class="bg-white bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-inbox fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <!-- Breakdown Stats -->
                @if(Auth::user()->role == 'admin')
                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.65rem;">
                            <i class="fas fa-chart-pie me-1"></i>Breakdown Posisi Surat
                        </small>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-masuk.index', ['filter' => 'pending_kabid']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #fff5f5; border-left: 3px solid #dc3545 !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-user-tie text-danger mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-danger mb-0">{{ $sm_pending_kabid }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">MENUNGGU KABID</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-masuk.index', ['filter' => 'pending_kasi']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #fff9e6; border-left: 3px solid #ffc107 !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-user-tie text-warning mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-warning mb-0">{{ $sm_pending_kasi }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">PROSES KASI</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-masuk.index', ['filter' => 'pending_staf']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #e8f4fd; border-left: 3px solid #0dcaf0 !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-user text-info mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-info mb-0">{{ $sm_pending_staf }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">PELAKSANA STAF</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                @else
                @php
                $linkPending = route('disposisi.index', ['filter' => 'unread']);
                $linkSelesai = route('disposisi.index', ['filter' => 'read']);
                @endphp

                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.65rem;">
                            <i class="fas fa-tasks me-1"></i>Status Pekerjaan
                        </small>
                    </div>

                    <div class="col-6">
                        <a href="{{ $linkPending }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #fff9e6; border-left: 3px solid #ffc107 !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-warning mb-0">{{ $sm_pending }}</h4>
                                        <i class="fas fa-envelope text-warning"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Belum Dibaca</small>
                                    <div class="progress mt-2" style="height: 3px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $sm_total > 0 ? ($sm_pending / $sm_total * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="{{ $linkSelesai }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #e8f5e9; border-left: 3px solid #198754 !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-success mb-0">{{ $sm_selesai }}</h4>
                                        <i class="fas fa-check-double text-success"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Sudah Dibaca</small>
                                    <div class="progress mt-2" style="height: 3px;">
                                        <div class="progress-bar bg-success" style="width: {{ $sm_total > 0 ? ($sm_selesai / $sm_total * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Surat Keluar -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-paper-plane text-danger me-2"></i>
                    <div>
                        <h6 class="mb-0 fw-bold" style="font-size: 0.9rem;">
                            @if(in_array(Auth::user()->role, ['kabid', 'kasi']))
                            Tugas Validasi Saya
                            @else
                            Surat Keluar
                            @endif
                        </h6>
                        <small class="text-muted" style="font-size: 0.7rem;">Monitoring & approval</small>
                    </div>
                </div>
            </div>

            <div class="card-body p-3">
                @if(in_array(Auth::user()->role, ['kabid', 'kasi']))

                <!-- Validation Counter for Pimpinan -->
                <a href="{{ route('surat-keluar.index', ['filter' => 'proses']) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm mb-3 card-hover" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold mb-1" style="font-size: 0.7rem;">PERLU VALIDASI ANDA</div>
                                    <h2 class="fw-bold text-white mb-0">{{ $sk_proses }}</h2>
                                </div>
                                <div class="bg-white bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-gavel fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.65rem;">
                            <i class="fas fa-history me-1"></i>Riwayat Keputusan Anda
                        </small>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'history_acc']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #e8f5e9; border-left: 3px solid #198754 !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-check-circle text-success mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-success mb-0">{{ $sk_acc }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">DISETUJUI</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'history_revisi']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #fff9e6; border-left: 3px solid #ffc107 !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-edit text-warning mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-warning mb-0">{{ $sk_revisi }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">REVISI</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-4">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'history_ditolak']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #f5f5f5; border-left: 3px solid #6c757d !important;">
                                <div class="card-body p-2 text-center">
                                    <i class="fas fa-times-circle text-dark mb-1" style="font-size: 1.2rem;"></i>
                                    <h4 class="fw-bold text-dark mb-0">{{ $sk_ditolak }}</h4>
                                    <small class="text-muted fw-semibold d-block" style="font-size: 0.65rem;">DITOLAK</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                @else

                <!-- Main Counter for Staff/Admin -->
                <a href="{{ route('surat-keluar.index') }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm mb-3 card-hover" style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-white-50 small fw-bold mb-1" style="font-size: 0.7rem;">TOTAL SURAT KELUAR</div>
                                    <h2 class="fw-bold text-white mb-0">{{ $sk_total }}</h2>
                                </div>
                                <div class="bg-white bg-opacity-10 rounded-circle p-2">
                                    <i class="fas fa-share-square fa-2x text-white"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <div class="row g-2">
                    <div class="col-12">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2" style="font-size: 0.65rem;">
                            <i class="fas fa-chart-line me-1"></i>Status Dokumen
                        </small>
                    </div>

                    <div class="col-6">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'proses']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #e7f3ff; border-left: 3px solid #0d6efd !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-primary mb-0">{{ $sk_proses }}</h4>
                                        <i class="fas fa-spinner fa-pulse text-primary"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Proses Validasi</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'revisi']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #fff9e6; border-left: 3px solid #ffc107 !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-warning mb-0">{{ $sk_revisi }}</h4>
                                        <i class="fas fa-tools text-warning"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Perlu Revisi</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'ditolak']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #f5f5f5; border-left: 3px solid #6c757d !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-dark mb-0">{{ $sk_ditolak }}</h4>
                                        <i class="fas fa-ban text-dark"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Ditolak</small>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="col-6">
                        <a href="{{ route('surat-keluar.index', ['filter' => 'acc']) }}" class="text-decoration-none">
                            <div class="card border-0 h-100 card-hover" style="background-color: #e8f5e9; border-left: 3px solid #198754 !important;">
                                <div class="card-body p-2">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h4 class="fw-bold text-success mb-0">{{ $sk_acc }}</h4>
                                        <i class="fas fa-check-circle text-success"></i>
                                    </div>
                                    <small class="text-muted fw-semibold text-uppercase d-block" style="font-size: 0.65rem;">Selesai (ACC)</small>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bottom Stats -->
<div class="row g-3 mt-2">
    @if(Auth::user()->role == 'admin')
    <!-- Card Pengguna Aktif - Hanya untuk Admin -->
    <div class="col-md-4">
        <a href="{{ route('users.index') }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100 card-hover">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                            <i class="fas fa-users fa-lg text-info"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $total_user }}</h4>
                            <small class="text-muted fw-semibold text-uppercase" style="font-size: 0.7rem;">Pengguna Aktif</small>
                        </div>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100" style="background-color: #f8f9fa;">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-white rounded-circle p-2 shadow-sm me-3">
                    <i class="fas fa-chart-line fa-lg text-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">Ringkasan Produktivitas</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        Efisiensi sistem: <strong class="text-success">{{ $total_user }}</strong> pengguna mengelola <strong class="text-primary">{{ $sm_total + $sk_total }}</strong> dokumen
                    </p>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Card Full Width untuk Non-Admin -->
    <div class="col-12">
        <div class="card border-0 shadow-sm h-100" style="background-color: #f8f9fa;">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-white rounded-circle p-2 shadow-sm me-3">
                    <i class="fas fa-chart-line fa-lg text-primary"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-1" style="font-size: 0.85rem;">Ringkasan Produktivitas</h6>
                    <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                        @if(Auth::user()->role == 'staff')
                        Tingkat penyelesaian: <strong class="text-success">{{ $sm_total > 0 ? round(($sm_selesai / $sm_total) * 100) : 0 }}%</strong>
                        @elseif(in_array(Auth::user()->role, ['kabid', 'kasi']))
                        Total keputusan: <strong class="text-primary">{{ $sk_acc + $sk_revisi + $sk_ditolak }}</strong> surat telah diproses
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<style>
    .card-hover {
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    }

    .progress {
        background-color: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }

    .progress-bar {
        border-radius: 10px;
    }

    .approval-metric {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-left: 5px solid #0f766e;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        color: #0f172a;
        min-height: 128px;
        padding: 18px;
    }

    .approval-metric span,
    .approval-metric small {
        color: #64748b;
        display: block;
        font-size: .78rem;
        font-weight: 800;
        text-transform: uppercase;
    }

    .approval-metric strong {
        display: block;
        font-size: 2.3rem;
        line-height: 1.1;
        margin: 8px 0;
    }

    .approval-metric.gold {
        border-left-color: #d8a030;
    }

    .approval-metric.success {
        border-left-color: #16a34a;
    }

    .approval-metric.muted {
        border-left-color: #64748b;
    }

    .approval-guide {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
    }

    .approval-guide div {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px;
    }

    .approval-guide i,
    .approval-guide strong,
    .approval-guide span {
        display: block;
    }

    .approval-guide i {
        color: #0f766e;
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .approval-guide span {
        color: #64748b;
        font-size: .82rem;
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .approval-guide {
            grid-template-columns: 1fr;
        }
    }
</style>

@endsection
