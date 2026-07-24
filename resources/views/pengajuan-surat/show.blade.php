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

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

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

        @php
        $canStartKasi = Auth::id() === $pengajuanSurat->posisi_saat_ini && Auth::user()->role === 'kasi' && $pengajuanSurat->status === 'diajukan';
        $canReviewKasi = Auth::id() === $pengajuanSurat->posisi_saat_ini && Auth::user()->role === 'kasi' && $pengajuanSurat->status === 'diperiksa_kasi';
        $canStartKabid = Auth::id() === $pengajuanSurat->posisi_saat_ini && Auth::user()->role === 'kabid' && $pengajuanSurat->status === 'disetujui_kasi';
        $canReviewKabid = Auth::id() === $pengajuanSurat->posisi_saat_ini && Auth::user()->role === 'kabid' && $pengajuanSurat->status === 'diperiksa_kabid';
        $canResubmit = Auth::id() === $pengajuanSurat->pemohon_id && $pengajuanSurat->status === 'draft';
        @endphp

        @if($canStartKasi || $canReviewKasi || $canStartKabid || $canReviewKabid || $canResubmit)
        <div class="detail-panel mb-3">
            <div class="detail-panel-header">
                <strong><i class="fas fa-gavel me-2 text-primary"></i>Panel Aksi</strong>
                <div class="small text-muted mt-1">Catatan akan masuk ke riwayat aktivitas pengajuan.</div>
            </div>
            <div class="p-3">
                <form action="{{ route('pengajuan-surat.process', $pengajuanSurat) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" placeholder="Tulis catatan pemeriksaan, revisi, atau alasan penolakan..."></textarea>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @if($canStartKasi || $canStartKabid)
                        <button name="aksi" value="periksa" class="btn btn-primary">
                            <i class="fas fa-play me-1"></i>Mulai Periksa
                        </button>
                        @endif
                        @if($canReviewKasi || $canReviewKabid)
                        <button name="aksi" value="acc" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Setujui
                        </button>
                        <button name="aksi" value="revisi" class="btn btn-warning" onclick="return confirm('Kembalikan pengajuan ini untuk revisi?');">
                            <i class="fas fa-rotate-left me-1"></i>Revisi
                        </button>
                        <button name="aksi" value="ditolak" class="btn btn-danger" onclick="return confirm('Tolak pengajuan ini secara permanen?');">
                            <i class="fas fa-ban me-1"></i>Tolak
                        </button>
                        @endif
                        @if($canResubmit)
                        <button name="aksi" value="ajukan_ulang" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-1"></i>Ajukan Ulang
                        </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
        @endif

        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <strong><i class="fas fa-file-contract me-2 text-primary"></i>Data Persyaratan</strong>
                    <div class="small text-muted mt-1">Preview dokumen tampil dalam modal. Unduh PDF atau DOCX setelah dicek.</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#documentPreviewModal">
                        <i class="fas fa-eye me-1"></i>Lihat Dokumen
                    </button>
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

        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <strong><i class="fas fa-signature me-2 text-primary"></i>Digital Signature</strong>
                    <div class="small text-muted mt-1">Tanda tangan digital memakai hash SHA-512 dan RSA milik Kabid.</div>
                </div>
                @if(Auth::user()->role === 'kabid' && $pengajuanSurat->status === 'disetujui_kabid' && ! $pengajuanSurat->digitalSignature)
                <form action="{{ route('pengajuan-surat.sign', $pengajuanSurat) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-primary" onclick="return confirm('Tandatangani dokumen ini secara digital?');">
                        <i class="fas fa-pen-nib me-1"></i>Tandatangani
                    </button>
                </form>
                @endif
            </div>
            <div class="signature-panel">
                @if($pengajuanSurat->digitalSignature)
                <div class="signature-state signed">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>Dokumen sudah ditandatangani</strong>
                        <span>Oleh {{ $pengajuanSurat->digitalSignature->signer->name }} pada {{ $pengajuanSurat->digitalSignature->signed_at->format('d/m/Y H:i') }} WIB</span>
                    </div>
                </div>
                <div class="signature-grid">
                    <div>
                        <span>Algoritma</span>
                        <strong>{{ $pengajuanSurat->digitalSignature->algorithm }}</strong>
                    </div>
                    <div>
                        <span>Hash dokumen</span>
                        <code>{{ $pengajuanSurat->digitalSignature->document_hash }}</code>
                    </div>
                </div>
                @else
                <div class="signature-state pending">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Belum ditandatangani</strong>
                        <span>Tombol tanda tangan akan aktif untuk Kabid setelah status menjadi Disetujui Kabid.</span>
                    </div>
                </div>
                @endif
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
        <div class="detail-panel mb-3">
            <div class="detail-panel-header">
                <strong><i class="fas fa-info-circle me-2 text-primary"></i>Catatan Alur</strong>
            </div>
            <div class="p-3">
                <p class="small text-muted mb-3">
                    Status bergerak dari Staff ke Kasi, lalu Kabid. Setelah Kabid menyetujui final, tombol tanda tangan digital akan aktif untuk Kabid.
                </p>
                <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-light border w-100">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>

        <div class="detail-panel">
            <div class="detail-panel-header">
                <strong><i class="fas fa-clock-rotate-left me-2 text-primary"></i>Riwayat Aktivitas</strong>
            </div>
            <div class="activity-list">
                @forelse($pengajuanSurat->riwayat as $riwayat)
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div>
                        <div class="activity-title">{{ str_replace('_', ' ', strtoupper($riwayat->aksi)) }}</div>
                        <div class="small text-muted">
                            {{ $riwayat->actor->name }} • {{ $riwayat->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="small">
                            <span class="text-muted">{{ $riwayat->status_sebelum ?: '-' }}</span>
                            <i class="fas fa-arrow-right mx-1 text-muted"></i>
                            <strong>{{ $riwayat->status_sesudah }}</strong>
                        </div>
                        @if($riwayat->catatan)
                        <div class="activity-note">{{ $riwayat->catatan }}</div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-3 text-muted small">Belum ada riwayat aktivitas.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade document-preview-modal" id="documentPreviewModal" tabindex="-1" aria-labelledby="documentPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="section-kicker">Preview Dokumen</div>
                    <h5 class="modal-title mb-0" id="documentPreviewModalLabel">{{ $pengajuanSurat->jenisSurat->nama }}</h5>
                    <small class="text-muted">{{ $pengajuanSurat->nomor_pengajuan }}</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <iframe
                    src="{{ route('pengajuan-surat.preview', $pengajuanSurat) }}?embed=1"
                    title="Preview dokumen {{ $pengajuanSurat->nomor_pengajuan }}"
                    class="document-preview-frame"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button>
                <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'pdf']) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-pdf me-1"></i>Download PDF
                </a>
                <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'docx']) }}" class="btn btn-primary">
                    <i class="fas fa-file-word me-1"></i>Download DOCX
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

    .signature-panel {
        padding: 16px;
    }

    .signature-state {
        align-items: center;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        padding: 14px;
    }

    .signature-state i {
        font-size: 1.35rem;
    }

    .signature-state strong,
    .signature-state span {
        display: block;
    }

    .signature-state span {
        color: #64748b;
        font-size: .84rem;
        margin-top: 2px;
    }

    .signature-state.signed {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .signature-state.pending {
        background: #fffbeb;
        border: 1px solid #fde68a;
        color: #92400e;
    }

    .signature-grid {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: grid;
        gap: 0;
        grid-template-columns: 180px 1fr;
        margin-top: 12px;
        overflow: hidden;
    }

    .signature-grid div {
        padding: 12px;
    }

    .signature-grid div:first-child {
        border-right: 1px solid #e2e8f0;
    }

    .signature-grid span {
        color: #64748b;
        display: block;
        font-size: .72rem;
        font-weight: 800;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .signature-grid code {
        display: block;
        font-size: .72rem;
        white-space: normal;
        word-break: break-all;
    }

    .activity-list {
        padding: 10px 14px;
    }

    .activity-item {
        display: grid;
        gap: 10px;
        grid-template-columns: 12px 1fr;
        padding: 12px 0;
        position: relative;
    }

    .activity-item:not(:last-child) {
        border-bottom: 1px solid #edf2f7;
    }

    .activity-dot {
        background: #0f766e;
        border-radius: 999px;
        height: 10px;
        margin-top: 6px;
        width: 10px;
    }

    .activity-title {
        color: #0f172a;
        font-size: .78rem;
        font-weight: 900;
        letter-spacing: .04em;
    }

    .activity-note {
        background: #f8fafc;
        border-left: 3px solid #0f766e;
        color: #475569;
        font-size: .82rem;
        margin-top: 8px;
        padding: 8px 10px;
    }

    .document-preview-modal .modal-content {
        border: 0;
        border-radius: 10px;
        overflow: hidden;
    }

    .document-preview-modal .modal-header,
    .document-preview-modal .modal-footer {
        background: #f8fafc;
        border-color: #e7edf3;
    }

    .document-preview-modal .modal-body {
        background: #e2e8f0;
        padding: 14px;
    }

    .document-preview-frame {
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        height: 72vh;
        width: 100%;
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

        .signature-grid {
            grid-template-columns: 1fr;
        }

        .signature-grid div:first-child {
            border-bottom: 1px solid #e2e8f0;
            border-right: 0;
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
