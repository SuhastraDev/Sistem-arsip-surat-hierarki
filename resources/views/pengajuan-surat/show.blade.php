@extends('layouts.main')

@section('title', 'Detail Pengajuan Surat')

@section('content')
@php
$trackingStyles = [
    'diajukan' => [
        'label' => 'Diajukan',
        'owner' => 'Staff',
        'description' => 'Pengajuan dibuat dan masuk ke antrean pemeriksaan.',
        'icon' => 'fas fa-paper-plane',
        'tone' => 'info',
    ],
    'periksa_kasi' => [
        'label' => 'Mulai Diperiksa Kasi',
        'owner' => 'Kasi',
        'description' => 'Kasi memeriksa kelengkapan dan isi pengajuan.',
        'icon' => 'fas fa-user-check',
        'tone' => 'info',
    ],
    'acc_kasi' => [
        'label' => 'Disetujui Kasi',
        'owner' => 'Kasi',
        'description' => 'Pengajuan lolos pemeriksaan Kasi dan diteruskan ke Kabid.',
        'icon' => 'fas fa-check',
        'tone' => 'success',
    ],
    'periksa_kabid' => [
        'label' => 'Mulai Diperiksa Kabid',
        'owner' => 'Kabid',
        'description' => 'Kabid melakukan pemeriksaan akhir.',
        'icon' => 'fas fa-user-tie',
        'tone' => 'info',
    ],
    'acc_kabid' => [
        'label' => 'Disetujui Kabid',
        'owner' => 'Kabid',
        'description' => 'Pengajuan disetujui final sebelum tanda tangan.',
        'icon' => 'fas fa-stamp',
        'tone' => 'success',
    ],
    'revisi' => [
        'label' => 'Perlu Revisi',
        'owner' => 'Reviewer',
        'description' => 'Pengajuan dikembalikan ke Staff. Staff dapat memperbaiki lalu klik Ajukan Ulang.',
        'icon' => 'fas fa-rotate-left',
        'tone' => 'warning',
    ],
    'ajukan_ulang' => [
        'label' => 'Diajukan Ulang',
        'owner' => 'Staff',
        'description' => 'Revisi dikirim ulang ke Kasi untuk pemeriksaan berikutnya.',
        'icon' => 'fas fa-paper-plane',
        'tone' => 'info',
    ],
    'ditolak' => [
        'label' => 'Ditolak',
        'owner' => 'Reviewer',
        'description' => 'Pengajuan ditolak. Alur berhenti dan tidak dapat diajukan ulang dari pengajuan ini.',
        'icon' => 'fas fa-ban',
        'tone' => 'danger',
    ],
    'tandatangan_kabid' => [
        'label' => 'Final Kembali ke Staff',
        'owner' => 'Kabid',
        'description' => 'Kabid menandatangani dokumen, sistem memasukkan QR/kode verifikasi, lalu hasil kembali ke Staff pemohon.',
        'icon' => 'fas fa-qrcode',
        'tone' => 'success',
    ],
];
$riwayatTimeline = $pengajuanSurat->riwayat->sortBy('created_at')->values();
$lastHistory = $riwayatTimeline->last();
$trackingItems = $riwayatTimeline->map(function ($riwayat) use ($trackingStyles, $pengajuanSurat, $lastHistory) {
    $style = $trackingStyles[$riwayat->aksi] ?? [
        'label' => str_replace('_', ' ', ucfirst($riwayat->aksi)),
        'owner' => ucfirst($riwayat->metadata['actor_role'] ?? $riwayat->actor?->role ?? 'Sistem'),
        'description' => 'Aktivitas pengajuan tercatat pada riwayat.',
        'icon' => 'fas fa-circle-dot',
        'tone' => 'info',
    ];
    $isLastMatchingCurrent = $lastHistory?->id === $riwayat->id && $pengajuanSurat->status === $riwayat->status_sesudah;
    $state = 'done';

    if ($riwayat->aksi === 'ditolak') {
        $state = 'rejected';
    } elseif ($riwayat->aksi === 'revisi' && $pengajuanSurat->status === 'draft' && $isLastMatchingCurrent) {
        $state = 'revision';
    } elseif ($isLastMatchingCurrent && ! in_array($pengajuanSurat->status, ['selesai'], true)) {
        $state = 'current';
    }

    return [
        'style' => $style,
        'state' => $state,
        'riwayat' => $riwayat,
    ];
});

if ($pengajuanSurat->status === 'disetujui_kabid' && ! $pengajuanSurat->digitalSignature) {
    $trackingItems->push([
        'style' => [
            'label' => 'Menunggu TTD Kabid',
            'owner' => 'Kabid',
            'description' => 'Kabid perlu menekan tombol tanda tangan agar dokumen final kembali ke Staff.',
            'icon' => 'fas fa-pen-nib',
            'tone' => 'warning',
        ],
        'state' => 'current',
        'riwayat' => null,
    ]);
}

if ($trackingItems->isEmpty()) {
    $trackingItems->push([
        'style' => [
            'label' => 'Belum Ada Riwayat',
            'owner' => 'Sistem',
            'description' => 'Pengajuan belum memiliki aktivitas tercatat.',
            'icon' => 'fas fa-clock',
            'tone' => 'info',
        ],
        'state' => 'current',
        'riwayat' => null,
    ]);
}
@endphp
@php
$verificationCode = $pengajuanSurat->digitalSignature?->verification_code;
$verificationUrl = $verificationCode ? route('verification.show', $verificationCode) : null;
@endphp

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($pengajuanSurat->status === 'selesai')
<div class="alert alert-success">
    <strong>Dokumen final sudah kembali ke Staff pemohon.</strong>
    QR verifikasi sudah ditempatkan di area TTD. PDF dan DOCX final membawa QR scannable serta kode verifikasi.
</div>
@endif
@if($pengajuanSurat->status === 'draft')
<div class="alert alert-warning">
    <strong>Pengajuan perlu revisi.</strong>
    Staff pemohon dapat memperbaiki data/lampiran yang diminta, lalu klik <strong>Ajukan Ulang</strong> agar kembali ke meja Kasi.
</div>
@endif
@if($pengajuanSurat->status === 'ditolak')
<div class="alert alert-danger">
    <strong>Pengajuan ditolak.</strong>
    Alur pengajuan ini sudah berhenti. Catatan penolakan dapat dilihat pada tracking dan riwayat aktivitas.
</div>
@endif
@if(Auth::user()->role === 'kabid' && $pengajuanSurat->status === 'disetujui_kabid' && ! $pengajuanSurat->digitalSignature)
<div class="kabid-sign-guide mb-3">
    <div class="guide-marker"><i class="fas fa-pen-nib"></i></div>
    <div>
        <span>Meja Kabid</span>
        <strong>Langkah berikutnya: tandatangani dokumen final</strong>
        <p>Buka preview untuk cek isi surat. Setelah tombol tanda tangan ditekan, sistem membuat kode verifikasi dan mengirim hasil final kembali ke Staff pemohon.</p>
    </div>
    <button type="button" class="btn btn-light border" data-bs-toggle="modal" data-bs-target="#documentPreviewModal">
        <i class="fas fa-eye me-1"></i>Cek Dokumen
    </button>
</div>
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
                    <div class="col-12">
                        <div class="template-origin">
                            <i class="fas fa-file-word"></i>
                            <div>
                                <span>Template sumber</span>
                                <strong>{{ $templateDefinition['template_label'] ?? ($pengajuanSurat->metadata['template_source'] ?? 'Template sistem') }}</strong>
                                <small>{{ $templateDefinition['template_note'] ?? 'Data pengajuan mengikuti template surat yang aktif.' }}</small>
                            </div>
                        </div>
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
        $canDeletePengajuan = Auth::user()->role === 'staff' && Auth::id() === $pengajuanSurat->pemohon_id && in_array($pengajuanSurat->status, ['draft', 'diajukan'], true);
        $hasProcessAction = $canStartKasi || $canReviewKasi || $canStartKabid || $canReviewKabid || $canResubmit;
        @endphp

        @if($hasProcessAction || $canDeletePengajuan)
        <div class="detail-panel mb-3">
            <div class="detail-panel-header">
                <strong><i class="fas fa-gavel me-2 text-primary"></i>Panel Aksi</strong>
                <div class="small text-muted mt-1">Catatan akan masuk ke riwayat aktivitas pengajuan.</div>
            </div>
            <div class="p-3">
                @if($hasProcessAction)
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
                @endif
                @if($canDeletePengajuan)
                <form action="{{ route('pengajuan-surat.destroy', $pengajuanSurat) }}" method="POST" class="{{ $hasProcessAction ? 'mt-3' : '' }}" onsubmit="return confirm('Hapus pengajuan ini? Riwayat dan lampiran pengajuan akan ikut dihapus.');">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger">
                        <i class="fas fa-trash me-1"></i>Hapus Pengajuan
                    </button>
                    <div class="form-text">Hanya tersedia sebelum pengajuan mulai diperiksa atau diterima oleh Kasi/Kabid.</div>
                </form>
                @endif
            </div>
        </div>
        @endif

        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <strong><i class="fas fa-file-contract me-2 text-primary"></i>Data Persyaratan</strong>
                    <div class="small text-muted mt-1">Preview dokumen tampil dalam modal. Setelah Kabid tanda tangan, QR verifikasi berada di area TTD dan PDF final membawa QR scannable.</div>
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
                    @php
                    $rawValue = $pengajuanSurat->metadata['form_data'][$row['key']] ?? null;
                    @endphp
                    @if(is_array($rawValue) && isset($rawValue['original_name']))
                    <a href="{{ route('pengajuan-surat.attachment', [$pengajuanSurat, $row['key']]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-paperclip me-1"></i>{{ $rawValue['original_name'] }}
                    </a>
                    @else
                    <strong>{{ $row['value'] }}</strong>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="detail-panel mb-3">
            <div class="detail-panel-header d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <strong><i class="fas fa-signature me-2 text-primary"></i>Tanda Tangan & Verifikasi</strong>
                    <div class="small text-muted mt-1">Kabid menandatangani dokumen, lalu sistem otomatis mengembalikan hasil final ke Staff pemohon.</div>
                </div>
                @if(Auth::user()->role === 'kabid' && $pengajuanSurat->status === 'disetujui_kabid' && ! $pengajuanSurat->digitalSignature)
                <form action="{{ route('pengajuan-surat.sign', $pengajuanSurat) }}" method="POST">
                    @csrf
                    <button class="btn btn-sm btn-primary" onclick="return confirm('Tandatangani dokumen dan kirim hasil final ke Staff pemohon?');">
                        <i class="fas fa-pen-nib me-1"></i>Tandatangani & Kirim ke Staff
                    </button>
                </form>
                @endif
            </div>
            <div class="signature-panel">
                @if($pengajuanSurat->digitalSignature)
                <div class="signature-state signed">
                    <i class="fas fa-circle-check"></i>
                    <div>
                        <strong>Dokumen final sudah ditandatangani dan dikirim ke Staff</strong>
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
                @if($verificationUrl)
                <div class="verification-box">
                    <div class="verification-qr">
                        <img src="{{ route('verification.qr', $verificationCode) }}" alt="QR verifikasi {{ $verificationCode }}">
                    </div>
                    <div>
                        <span class="verification-kicker">Kode Verifikasi</span>
                        <code>{{ $verificationCode }}</code>
                        <p class="small text-muted mb-2">QR tampil di area TTD preview web. PDF final membawa QR verifikasi di area TTD.</p>
                        <a href="{{ $verificationUrl }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-shield-halved me-1"></i>Buka Verifikasi
                        </a>
                    </div>
                </div>
                @endif
                @else
                <div class="signature-state pending">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>Belum ditandatangani</strong>
                        <span>Tombol tanda tangan aktif untuk Kabid setelah status menjadi Disetujui Kabid.</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="detail-panel">
            <div class="detail-panel-header">
                <strong><i class="fas fa-route me-2 text-primary"></i>Tracking Pengajuan</strong>
                <div class="small text-muted mt-1">Mengikuti kondisi sebenarnya: revisi bisa diajukan ulang, ditolak berhenti, dan selesai kembali ke Staff.</div>
            </div>
            <div class="p-4">
                <div class="approval-roadmap">
                    @foreach($trackingItems as $item)
                    @php
                    $step = $item['style'];
                    $state = $item['state'];
                    $riwayat = $item['riwayat'];
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
                                    @if($riwayat?->catatan)
                                    <div class="roadmap-note">{{ $riwayat->catatan }}</div>
                                    @endif
                                    @if($riwayat)
                                    <div class="roadmap-meta">
                                        {{ $riwayat->actor?->name ?? 'Sistem' }} • {{ $riwayat->created_at->format('d/m/Y H:i') }}
                                    </div>
                                    @endif
                                </div>
                                <span class="roadmap-state">
                                    @if($state === 'done')
                                    Selesai
                                    @elseif($state === 'current')
                                    Aktif
                                    @elseif($state === 'revision')
                                    Revisi
                                    @elseif($state === 'rejected')
                                    Ditolak
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
                    Status bergerak dari Staff ke Kasi, lalu Kabid. Setelah Kabid menandatangani, dokumen final otomatis kembali ke Staff pemohon dan bisa diverifikasi memakai QR atau kode.
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

    .kabid-sign-guide {
        align-items: center;
        background: linear-gradient(135deg, #064e3b, #0f766e);
        border-radius: 8px;
        box-shadow: 0 14px 32px rgba(15, 118, 110, .18);
        color: #fff;
        display: grid;
        gap: 14px;
        grid-template-columns: 48px 1fr auto;
        padding: 16px;
    }

    .guide-marker {
        align-items: center;
        background: rgba(255, 255, 255, .14);
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 8px;
        display: flex;
        height: 48px;
        justify-content: center;
        width: 48px;
    }

    .kabid-sign-guide span {
        color: #a7f3d0;
        display: block;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .kabid-sign-guide strong {
        display: block;
        font-size: 1rem;
    }

    .kabid-sign-guide p {
        color: #d1fae5;
        font-size: .84rem;
        margin: 2px 0 0;
    }

    .template-origin {
        align-items: center;
        background: #f8fafc;
        border: 1px dashed #99f6e4;
        border-radius: 8px;
        display: grid;
        gap: 12px;
        grid-template-columns: 40px 1fr;
        padding: 12px;
    }

    .template-origin i {
        align-items: center;
        background: #ecfdf5;
        border-radius: 8px;
        color: #0f766e;
        display: flex;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .template-origin span,
    .template-origin small {
        color: #64748b;
        display: block;
        font-size: .75rem;
    }

    .template-origin strong {
        color: #064e3b;
        display: block;
        font-size: .92rem;
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

    .verification-box {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #dbe5ef;
        border-radius: 8px;
        display: grid;
        gap: 14px;
        grid-template-columns: 136px 1fr;
        margin-top: 12px;
        padding: 14px;
    }

    .verification-qr {
        align-items: center;
        background: #fff;
        border: 1px solid #dbe5ef;
        border-radius: 8px;
        display: flex;
        height: 136px;
        justify-content: center;
        overflow: hidden;
        width: 136px;
    }

    .verification-qr img {
        display: block;
        height: 136px;
        width: 136px;
    }

    .verification-kicker {
        color: #0f766e;
        display: block;
        font-size: .72rem;
        font-weight: 900;
        letter-spacing: .1em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .verification-box code {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        border-radius: 6px;
        color: #166534;
        display: inline-block;
        font-weight: 800;
        margin-bottom: 8px;
        padding: 5px 8px;
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

    .roadmap-note {
        background: #fff;
        border-left: 3px solid #0f766e;
        color: #475569;
        font-size: .82rem;
        margin-top: 10px;
        padding: 8px 10px;
    }

    .roadmap-meta {
        color: #64748b;
        font-size: .76rem;
        font-weight: 700;
        margin-top: 8px;
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

    .roadmap-item.revision .roadmap-node {
        background: #f59e0b;
        border-color: #f59e0b;
        color: #fff;
        box-shadow: 0 0 0 5px rgba(245, 158, 11, .16);
    }

    .roadmap-item.revision .roadmap-card {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .roadmap-item.revision .roadmap-state {
        background: #fef3c7;
        color: #92400e;
    }

    .roadmap-item.rejected .roadmap-node {
        background: #ef4444;
        border-color: #ef4444;
        color: #fff;
        box-shadow: 0 0 0 5px rgba(239, 68, 68, .14);
    }

    .roadmap-item.rejected .roadmap-card {
        background: #fef2f2;
        border-color: #fecaca;
    }

    .roadmap-item.rejected .roadmap-state {
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

        .verification-box {
            grid-template-columns: 1fr;
        }

        .kabid-sign-guide {
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
