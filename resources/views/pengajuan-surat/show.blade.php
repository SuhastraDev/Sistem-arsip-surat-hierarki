@extends('layouts.main')

@section('title', 'Detail Pengajuan Surat')

@section('content')
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

        <div class="detail-panel">
            <div class="detail-panel-header">
                <strong><i class="fas fa-route me-2 text-primary"></i>Status Roadmap Pengajuan</strong>
            </div>
            <div class="p-4">
                @php
                $steps = [
                    'diajukan' => 'Diajukan',
                    'diperiksa_kasi' => 'Diperiksa Kasi',
                    'disetujui_kasi' => 'Disetujui Kasi',
                    'diperiksa_kabid' => 'Diperiksa Kabid',
                    'disetujui_kabid' => 'Disetujui Kabid',
                    'ditandatangani' => 'Ditandatangani',
                    'selesai' => 'Selesai',
                ];
                $keys = array_keys($steps);
                $currentIndex = array_search($pengajuanSurat->status, $keys, true);
                @endphp
                <div class="d-flex flex-wrap gap-2">
                    @foreach($steps as $value => $label)
                    @php $stepIndex = array_search($value, $keys, true); @endphp
                    <span class="badge {{ $currentIndex !== false && $stepIndex <= $currentIndex ? 'bg-success' : 'bg-secondary' }}">
                        {{ $label }}
                    </span>
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
</style>
@endsection
