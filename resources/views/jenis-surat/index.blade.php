@extends('layouts.main')

@section('title', 'Master Jenis Surat')

@section('content')
<div class="workbench-hero mb-4">
    <div>
        <div class="section-kicker">Master Data</div>
        <h4 class="fw-bold mb-1">Jenis Surat</h4>
        <p class="text-muted mb-0">Atur jenis surat yang tersedia untuk pengajuan. Tiga jenis utama menjadi dasar template pada fase berikutnya.</p>
    </div>
    <div class="hero-count">
        <span>{{ $jenisSurats->count() }}</span>
        <small>jenis surat</small>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="input-panel">
            <div class="input-panel-header">
                <div class="panel-icon"><i class="fas fa-plus"></i></div>
                <div>
                    <strong>Tambah jenis</strong>
                    <div class="small opacity-75">Jenis aktif muncul di form pengajuan.</div>
                </div>
            </div>
            <div class="p-4">
                @if(session('success'))
                <div class="alert alert-success compact-alert">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger compact-alert">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger compact-alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form action="{{ route('jenis-surat.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label field-label">Nama jenis surat</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="Contoh: Surat Cuti" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label field-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="5" placeholder="Ringkasan fungsi jenis surat">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="status-toggle mb-3">
                        <div>
                            <strong>Aktif</strong>
                            <small>Siap dipakai staff</small>
                        </div>
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                            <label class="visually-hidden" for="isActive">Aktif untuk pengajuan</label>
                        </div>
                    </div>
                    <button class="btn btn-primary w-100 save-button">
                        <i class="fas fa-save me-2"></i>Simpan jenis
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="list-panel">
            <div class="list-panel-header">
                <div>
                    <h5 class="mb-1 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i>Daftar jenis surat</h5>
                    <small class="text-muted">Master awal untuk Surat Cuti, Surat Tugas, dan Nota Dinas.</small>
                </div>
                <span class="badge bg-light text-dark border">{{ $jenisSurats->where('is_active', true)->count() }} aktif</span>
            </div>
            <div class="letter-type-list">
                @forelse($jenisSurats as $jenisSurat)
                <div class="letter-type-item">
                    <div class="type-marker">
                        <i class="fas fa-file-lines"></i>
                    </div>
                    <div class="type-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-2">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <h6 class="fw-bold mb-0">{{ $jenisSurat->nama }}</h6>
                                    <code>{{ $jenisSurat->slug }}</code>
                                </div>
                                <p class="text-muted small mb-0">{{ $jenisSurat->deskripsi ?: 'Belum ada deskripsi.' }}</p>
                            </div>
                            <div class="type-meta">
                                @if($jenisSurat->is_active)
                                <span class="status-pill active">Aktif</span>
                                @else
                                <span class="status-pill inactive">Nonaktif</span>
                                @endif
                                <span class="usage-count">{{ $jenisSurat->pengajuan_surats_count }} pengajuan</span>
                            </div>
                        </div>

                        <div class="type-actions">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editJenis{{ $jenisSurat->id }}">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                            <form action="{{ route('jenis-surat.destroy', $jenisSurat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jenis surat ini?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            </form>
                        </div>

                        <div class="collapse mt-3" id="editJenis{{ $jenisSurat->id }}">
                            <form action="{{ route('jenis-surat.update', $jenisSurat) }}" method="POST" class="edit-strip">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nama" class="form-control" value="{{ $jenisSurat->nama }}" required>
                                <input type="text" name="deskripsi" class="form-control" value="{{ $jenisSurat->deskripsi }}" placeholder="Deskripsi">
                                <label class="edit-toggle">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $jenisSurat->is_active ? 'checked' : '' }}>
                                    Aktif
                                </label>
                                <button class="btn btn-primary btn-sm">Update</button>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <strong>Belum ada jenis surat</strong>
                    <span>Tambahkan jenis surat pertama dari form di sebelah kiri.</span>
                </div>
                @endforelse
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

    .workbench-hero,
    .input-panel,
    .list-panel {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .workbench-hero {
        align-items: center;
        border-left: 5px solid #0f766e;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 24px;
    }

    .hero-count {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        min-width: 104px;
        padding: 12px;
        text-align: center;
    }

    .hero-count span {
        color: #0f172a;
        display: block;
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
    }

    .hero-count small {
        color: #64748b;
        font-size: .75rem;
    }

    .input-panel,
    .list-panel {
        overflow: hidden;
    }

    .input-panel-header {
        align-items: center;
        background: #0f766e;
        color: #fff;
        display: flex;
        gap: 12px;
        padding: 16px 18px;
    }

    .panel-icon {
        align-items: center;
        background: rgba(255, 255, 255, .16);
        border-radius: 8px;
        display: flex;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .field-label {
        color: #334155;
        font-size: .8rem;
        font-weight: 800;
    }

    .status-toggle {
        align-items: center;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        justify-content: space-between;
        padding: 12px 14px;
    }

    .status-toggle small {
        color: #64748b;
        display: block;
        font-size: .75rem;
    }

    .save-button {
        border-radius: 7px;
        font-weight: 700;
        padding: 10px 14px;
    }

    .compact-alert {
        border-radius: 8px;
        font-size: .88rem;
        padding: 10px 12px;
    }

    .list-panel-header {
        align-items: center;
        border-bottom: 1px solid #e7edf3;
        display: flex;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .letter-type-list {
        display: grid;
    }

    .letter-type-item {
        align-items: flex-start;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        gap: 14px;
        padding: 18px;
    }

    .letter-type-item:last-child {
        border-bottom: 0;
    }

    .type-marker {
        align-items: center;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        color: #1d4ed8;
        display: flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .type-body {
        flex: 1;
        min-width: 0;
    }

    .type-body code {
        background: #f1f5f9;
        border-radius: 5px;
        color: #475569;
        font-size: .72rem;
        padding: 3px 6px;
    }

    .type-meta {
        align-items: flex-end;
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 120px;
    }

    .status-pill,
    .usage-count {
        border-radius: 999px;
        display: inline-flex;
        font-size: .74rem;
        font-weight: 800;
        padding: 5px 10px;
        white-space: nowrap;
    }

    .status-pill.active {
        background: #ecfdf5;
        color: #166534;
    }

    .status-pill.inactive {
        background: #f1f5f9;
        color: #475569;
    }

    .usage-count {
        background: #fff7ed;
        color: #9a3412;
    }

    .type-actions {
        display: flex;
        gap: 8px;
        margin-top: 14px;
    }

    .edit-strip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        display: grid;
        gap: 10px;
        grid-template-columns: 1fr 1.4fr auto auto;
        padding: 12px;
    }

    .edit-toggle {
        align-items: center;
        color: #334155;
        display: inline-flex;
        font-size: .85rem;
        font-weight: 700;
        gap: 7px;
        white-space: nowrap;
    }

    .empty-state {
        align-items: center;
        color: #64748b;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 48px 20px;
        text-align: center;
    }

    .empty-state i {
        font-size: 2rem;
        opacity: .65;
    }

    @media (max-width: 768px) {
        .workbench-hero,
        .list-panel-header,
        .letter-type-item {
            align-items: flex-start;
            flex-direction: column;
        }

        .hero-count,
        .type-meta {
            align-items: flex-start;
            text-align: left;
        }

        .edit-strip {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
