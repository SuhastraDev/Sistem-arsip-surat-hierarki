@extends('layouts.main')

@section('title', 'Pengajuan Surat')

@section('content')
<div class="content-header bg-white p-3 rounded shadow-sm mb-3">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3">
        <div>
            <h5 class="mb-1">
                <i class="fas fa-file-signature me-2 text-primary"></i>Pengajuan Surat
            </h5>
            <small class="text-muted">Alur baru untuk Surat Cuti, Surat Tugas, dan Nota Dinas</small>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff', 'kasi']))
        <a href="{{ route('pengajuan-surat.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-1"></i>Buat Pengajuan
        </a>
        @endif
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="bg-white p-3 rounded shadow-sm mb-3">
    <form action="{{ route('pengajuan-surat.index') }}" method="GET">
        <div class="row g-3">
            <div class="col-lg-4">
                <label class="form-label text-secondary mb-1">Pencarian</label>
                <input type="text" name="search" class="form-control" placeholder="Cari perihal..." value="{{ request('search') }}">
            </div>
            <div class="col-lg-3">
                <label class="form-label text-secondary mb-1">Jenis Surat</label>
                <select name="jenis_surat_id" class="form-select">
                    <option value="">Semua Jenis</option>
                    @foreach($jenisSurats as $jenisSurat)
                    <option value="{{ $jenisSurat->id }}" {{ request('jenis_surat_id') == $jenisSurat->id ? 'selected' : '' }}>
                        {{ $jenisSurat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label text-secondary mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label text-secondary mb-1">Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua</option>
                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                    <option value="{{ $year }}" {{ request('tahun') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                @if(request()->hasAny(['search', 'jenis_surat_id', 'status', 'tahun']))
                <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-light border">Reset</a>
                @endif
                <button class="btn btn-primary">
                    <i class="fas fa-search me-1"></i>Cari
                </button>
            </div>
        </div>
    </form>
</div>

<div class="bg-white rounded shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th class="text-white">Nomor</th>
                    <th class="text-white">Tanggal</th>
                    <th class="text-white">Jenis</th>
                    <th class="text-white">Perihal</th>
                    <th class="text-white">Pemohon</th>
                    <th class="text-white">Status</th>
                    <th class="text-white">Posisi</th>
                    <th class="text-center text-white">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuanSurats as $pengajuan)
                <tr>
                    <td><code>{{ $pengajuan->nomor_pengajuan }}</code></td>
                    <td>{{ $pengajuan->tanggal_pengajuan->format('d/m/Y') }}</td>
                    <td><span class="badge bg-secondary">{{ $pengajuan->jenisSurat->nama }}</span></td>
                    <td>{{ Str::limit($pengajuan->perihal, 45) }}</td>
                    <td>
                        <div class="fw-semibold small">{{ $pengajuan->pemohon->name }}</div>
                        <div class="text-muted small">{{ $pengajuan->pemohon->jabatan }}</div>
                    </td>
                    <td>
                        @php
                        $statusColor = match($pengajuan->status) {
                            'ditolak' => 'danger',
                            'selesai', 'ditandatangani', 'disetujui_kabid' => 'success',
                            'draft' => 'secondary',
                            default => 'warning',
                        };
                        @endphp
                        <span class="badge bg-{{ $statusColor }}">{{ $pengajuan->status_label }}</span>
                    </td>
                    <td>
                        @if($pengajuan->posisi)
                        <div class="fw-semibold small">{{ $pengajuan->posisi->name }}</div>
                        <div class="text-muted small">{{ $pengajuan->posisi->jabatan }}</div>
                        @else
                        <span class="text-muted small">Belum ada posisi</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pengajuan-surat.show', $pengajuan) }}" class="btn btn-sm btn-light border">
                            <i class="fas fa-eye me-1"></i>Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 opacity-50"></i>
                        <h5>Belum Ada Pengajuan</h5>
                        <p class="text-muted mb-0">Data akan muncul setelah pengajuan surat dibuat.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pengajuanSurats->hasPages())
    <div class="p-3 border-top">
        {{ $pengajuanSurats->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
