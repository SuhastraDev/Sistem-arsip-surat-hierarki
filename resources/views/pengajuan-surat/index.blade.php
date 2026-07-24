@extends('layouts.main')

@section('title', 'Pengajuan Surat')

@section('content')
<div class="request-overview mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-end">
        <div>
            <div class="section-kicker">E-Surat</div>
            <h4 class="mb-1 fw-bold">Pengajuan Surat</h4>
            <p class="text-muted mb-0">Pantau permohonan Surat Cuti, Surat Tugas, dan Nota Dinas dari satu meja kerja.</p>
        </div>
        @if(in_array(Auth::user()->role, ['admin', 'staff', 'kasi']))
        <a href="{{ route('pengajuan-surat.create') }}" class="btn btn-primary btn-action">
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

<div class="filter-panel mb-3">
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

<div class="queue-panel">
    <div class="queue-panel-header">
        <div>
            <h6 class="mb-1 fw-bold">Daftar Pengajuan</h6>
            <small class="text-muted">Status menunjukkan kondisi dokumen, tahap menunjukkan meja yang sedang menunggu aksi.</small>
        </div>
        <span class="badge bg-light text-dark border">{{ $pengajuanSurats->total() }} data</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nomor</th>
                    <th>Tanggal</th>
                    <th>Jenis</th>
                    <th>Perihal</th>
                    <th>Pemohon</th>
                    <th>Status</th>
                    <th>Tahap</th>
                    <th class="text-center">Aksi</th>
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
                        <div class="stage-chip">
                            <i class="fas fa-location-dot"></i>
                            <span>{{ $pengajuan->tahap_label }}</span>
                        </div>
                        @if($pengajuan->posisi)
                        <div class="text-muted small mt-1">{{ $pengajuan->posisi->name }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('pengajuan-surat.show', $pengajuan) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye me-1"></i>Lihat
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
<style>
    .section-kicker {
        color: #0f766e;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .request-overview,
    .filter-panel,
    .queue-panel {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .request-overview {
        padding: 22px 24px;
        border-left: 5px solid #0f766e;
    }

    .filter-panel {
        padding: 18px;
    }

    .queue-panel-header {
        align-items: center;
        border-bottom: 1px solid #e7edf3;
        display: flex;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .queue-panel thead th {
        background: #f4f7fb;
        color: #334155;
        font-size: .76rem;
        font-weight: 800;
        letter-spacing: .04em;
        padding: 12px;
        text-transform: uppercase;
    }

    .queue-panel tbody td {
        padding: 14px 12px;
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

    .btn-action {
        border-radius: 7px;
        font-weight: 700;
        padding: 10px 14px;
    }

    @media (max-width: 768px) {
        .queue-panel-header {
            align-items: flex-start;
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endsection
