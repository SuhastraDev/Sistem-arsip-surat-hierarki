@extends('layouts.main')

@section('title', 'Detail Pengajuan Surat')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>{{ $pengajuanSurat->nomor_pengajuan }}</h5>
                    <small class="text-muted">{{ $pengajuanSurat->jenisSurat->nama }}</small>
                </div>
                <span class="badge bg-primary">{{ $pengajuanSurat->status_label }}</span>
            </div>
            <div class="card-body">
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

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <strong><i class="fas fa-route me-2 text-primary"></i>Status Roadmap Pengajuan</strong>
            </div>
            <div class="card-body">
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
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-light">
                <strong><i class="fas fa-info-circle me-2 text-primary"></i>Catatan Fase 1</strong>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Modul ini baru menyiapkan master jenis surat, struktur pengajuan, dan status awal. Form persyaratan, template HTML/PDF/DOCX, approval final, digital signature, dan QR code dikerjakan pada fase berikutnya.
                </p>
                <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-light border w-100">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
