@extends('layouts.main')

@section('title', 'Detail Pengajuan Surat')

@section('content')
@php
$steps = [
    'diajukan' => [
        'label' => 'Diajukan',
        'owner' => 'Staff',
        'description' => 'Pengajuan dibuat dan masuk ke antrean pemeriksaan.',
        'icon' => 'fas fa-paper-plane',
    ],
    'diperiksa_kasi' => [
        'label' => 'Diperiksa Kasi',
        'owner' => 'Kasi',
        'description' => 'Kasi memeriksa kelengkapan dan isi pengajuan.',
        'icon' => 'fas fa-user-check',
    ],
    'disetujui_kasi' => [
        'label' => 'Disetujui Kasi',
        'owner' => 'Kasi',
        'description' => 'Pengajuan lolos pemeriksaan Kasi.',
        'icon' => 'fas fa-check',
    ],
    'diperiksa_kabid' => [
        'label' => 'Diperiksa Kabid',
        'owner' => 'Kabid',
        'description' => 'Kabid melakukan pemeriksaan akhir.',
        'icon' => 'fas fa-user-tie',
    ],
    'disetujui_kabid' => [
        'label' => 'Disetujui Kabid',
        'owner' => 'Kabid',
        'description' => 'Pengajuan disetujui final sebelum tanda tangan.',
        'icon' => 'fas fa-stamp',
    ],
    'ditandatangani' => [
        'label' => 'Ditandatangani',
        'owner' => 'Kabid',
        'description' => 'Dokumen final ditandatangani secara digital.',
        'icon' => 'fas fa-signature',
    ],
    'selesai' => [
        'label' => 'Selesai',
        'owner' => 'Sistem',
        'description' => 'Dokumen final tersedia untuk unduh dan verifikasi.',
        'icon' => 'fas fa-box-archive',
    ],
];
$keys = array_keys($steps);
$currentIndex = array_search($pengajuanSurat->status, $keys, true);
$currentIndex = $currentIndex === false ? 0 : $currentIndex;
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <div class="section-kicker">Detail Pengajuan</div>
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>{{ $pengajuanSurat->nomor_pengajuan }}</h5>
                    <small class="text-muted">{{ $pengajuanSurat->jenisSurat->nama }}</small>
                </div>
                <div class="text-md-end">
                    <span class="badge bg-primary mb-2">{{ $pengajuanSurat->status_label }}</span>
                    <div class="stage-chip"><i class="fas fa-location-dot"></i>{{ $pengajuanSurat->tahap_label }}</div>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Pengajuan</div>
                        <div class="fw-semibold">{{ $pengajuanSurat->tanggal_pengajuan->format('d F Y') }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Pemohon</div>
                        <div class="fw-semibold">{{ $pengajuanSurat->pemohon->name }}</div>
                        <div class="small text-muted">{{ $pengajuanSurat->pemohon->jabatan }}</div>
                    </div>
                    <div class="col-12">
                        <div class="text-muted small">Perihal</div>
                        <div class="fw-semibold">{{ $pengajuanSurat->perihal }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Posisi Saat Ini</div>
                        @if($pengajuanSurat->posisi)
                        <div class="fw-semibold">{{ $pengajuanSurat->posisi->name }}</div>
                        <div class="small text-muted">{{ $pengajuanSurat->posisi->jabatan }}</div>
                        @else
                        <div class="text-muted">Belum ada posisi</div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Catatan Fase</div>
                        <div class="fw-semibold">{{ $pengajuanSurat->metadata['catatan'] ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <strong><i class="fas fa-file-contract me-2 text-primary"></i>Data Persyaratan</strong>
                    <div class="small text-muted mt-1">Data ini menjadi isi template HTML/PDF/DOCX.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('pengajuan-surat.preview', $pengajuanSurat) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-eye me-1"></i>Preview
                    </a>
                    <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'html']) }}" class="btn btn-sm btn-light border">HTML</a>
                    <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'pdf']) }}" class="btn btn-sm btn-light border">PDF</a>
                    <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'docx']) }}" class="btn btn-sm btn-light border">DOCX</a>
                </div>
            </div>
            <div class="requirement-summary">
                @foreach($templateRows as $row)
                <div class="requirement-row">
                    <span>{{ $row['label'] }}</span>
                    <strong>{{ $row['value'] }}</strong>
                </div>
                @endforeach
            </div>
        </div>

        <div class="detail-panel">
            <div class="detail-panel-header">
                <strong><i class="fas fa-route me-2 text-primary"></i>Roadmap Pengajuan</strong>
                <div class="small text-muted mt-1">Alur kerja dari staff sampai dokumen final siap diverifikasi.</div>
            </div>
            <div class="p-4">
                <div class="approval-roadmap">
                    @foreach($steps as $value => $step)
                    @php
                    $stepIndex = array_search($value, $keys, true);
                    $state = 'upcoming';
                    if ($pengajuanSurat->status === 'ditolak' && $stepIndex > $currentIndex) {
                        $state = 'blocked';
                    } elseif ($stepIndex < $currentIndex) {
                        $state = 'done';
                    } elseif ($stepIndex === $currentIndex) {
                        $state = $pengajuanSurat->status === 'ditolak' ? 'rejected' : 'current';
                    }
                    @endphp
                    <div class="roadmap-item {{ $state }}">
                        <div class="roadmap-node">
                            <i class="{{ $step['icon'] }}"></i>
                        </div>
                        <div class="roadmap-card">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                                <div>
                                    <div class="roadmap-owner">{{ $step['owner'] }}</div>
                                    <h6 class="mb-1 fw-bold">{{ $step['label'] }}</h6>
                                    <p class="mb-0 text-muted small">{{ $step['description'] }}</p>
                                </div>
                                <span class="roadmap-state">
                                    @if($state === 'done')
                                    Selesai
                                    @elseif($state === 'current')
                                    Aktif
                                    @elseif($state === 'rejected')
                                    Ditolak
                                    @elseif($state === 'blocked')
                                    Terhenti
                                    @else
                                    Menunggu
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="detail-panel">
            <div class="detail-panel-header">
                <strong><i class="fas fa-info-circle me-2 text-primary"></i>Catatan Fase 1</strong>
            </div>
            <div class="p-3">
                <p class="small text-muted mb-3">
                    Status `Diajukan` berarti pengajuan sudah masuk antrean. Jika tahapnya menampilkan Kasi, berarti dokumen sedang menunggu pemeriksaan Kasi.
                </p>
                <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-light border w-100">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>
<style>
    .section-kicker {
        color: #0f766e;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .detail-panel {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        overflow: hidden;
    }

    .detail-panel-header {
        background: #f8fafc;
        border-bottom: 1px solid #e7edf3;
        padding: 16px 18px;
    }

    .stage-chip {
        align-items: center;
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 999px;
        color: #166534;
        display: inline-flex;
        font-size: .78rem;
        font-weight: 700;
        gap: 7px;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .requirement-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .requirement-row {
        border-bottom: 1px solid #edf2f7;
        border-right: 1px solid #edf2f7;
        min-height: 82px;
        padding: 14px 16px;
    }

    .requirement-row:nth-child(2n) {
        border-right: 0;
    }

    .requirement-row span {
        color: #64748b;
        display: block;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .04em;
        margin-bottom: 6px;
        text-transform: uppercase;
    }

    .requirement-row strong {
        color: #0f172a;
        font-size: .92rem;
        font-weight: 700;
        white-space: pre-line;
    }

    .approval-roadmap {
        display: grid;
        gap: 0;
        position: relative;
    }

    .roadmap-item {
        display: grid;
        gap: 14px;
        grid-template-columns: 42px 1fr;
        position: relative;
    }

    .roadmap-item:not(:last-child) {
        padding-bottom: 18px;
    }

    .roadmap-item:not(:last-child)::before {
        background: #dbe5ef;
        bottom: 0;
        content: "";
        left: 20px;
        position: absolute;
        top: 42px;
        width: 2px;
    }

    .roadmap-node {
        align-items: center;
        background: #f1f5f9;
        border: 2px solid #dbe5ef;
        border-radius: 999px;
        color: #64748b;
        display: flex;
        height: 42px;
        justify-content: center;
        position: relative;
        width: 42px;
        z-index: 2;
    }

    .roadmap-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 14px 16px;
    }

    .roadmap-owner {
        color: #0f766e;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .roadmap-state {
        align-self: flex-start;
        border-radius: 999px;
        background: #e2e8f0;
        color: #475569;
        font-size: .72rem;
        font-weight: 800;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .roadmap-item.done .roadmap-node,
    .roadmap-item.done:not(:last-child)::before {
        background: #0f766e;
        border-color: #0f766e;
        color: #fff;
    }

    .roadmap-item.done .roadmap-card {
        background: #ecfdf5;
        border-color: #bbf7d0;
    }

    .roadmap-item.done .roadmap-state {
        background: #dcfce7;
        color: #166534;
    }

    .roadmap-item.current .roadmap-node {
        background: #f59e0b;
        border-color: #f59e0b;
        color: #fff;
        box-shadow: 0 0 0 5px rgba(245, 158, 11, .18);
    }

    .roadmap-item.current .roadmap-card {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .roadmap-item.current .roadmap-state {
        background: #fef3c7;
        color: #92400e;
    }

    .roadmap-item.rejected .roadmap-node,
    .roadmap-item.blocked .roadmap-node {
        background: #ef4444;
        border-color: #ef4444;
        color: #fff;
    }

    .roadmap-item.rejected .roadmap-card,
    .roadmap-item.blocked .roadmap-card {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .roadmap-item.rejected .roadmap-state,
    .roadmap-item.blocked .roadmap-state {
        background: #fee2e2;
        color: #991b1b;
    }

    @media (max-width: 576px) {
        .requirement-summary {
            grid-template-columns: 1fr;
        }

        .requirement-row {
            border-right: 0;
        }

        .roadmap-item {
            grid-template-columns: 34px 1fr;
        }

        .roadmap-node {
            height: 34px;
            width: 34px;
        }

        .roadmap-item:not(:last-child)::before {
            left: 16px;
            top: 34px;
        }
    }
</style>
@endsection
