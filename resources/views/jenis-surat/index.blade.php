@extends('layouts.main')

@section('title', 'Master Jenis Surat')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <strong><i class="fas fa-plus-circle me-2"></i>Tambah Jenis Surat</strong>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                <div class="alert alert-danger">
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
                        <label class="form-label fw-semibold">Nama Jenis Surat</label>
                        <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" placeholder="Contoh: Surat Cuti" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Ringkasan fungsi jenis surat">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" checked>
                        <label class="form-check-label" for="isActive">Aktif untuk pengajuan</label>
                    </div>
                    <button class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Simpan Jenis Surat
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0"><i class="fas fa-layer-group me-2 text-primary"></i>Daftar Jenis Surat</h5>
                    <small class="text-muted">Master awal untuk Surat Cuti, Surat Tugas, dan Nota Dinas</small>
                </div>
                <span class="badge bg-primary">{{ $jenisSurats->count() }} jenis</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Jenis Surat</th>
                            <th>Status</th>
                            <th class="text-center">Pengajuan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jenisSurats as $jenisSurat)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $jenisSurat->nama }}</div>
                                <div class="small text-muted">{{ $jenisSurat->deskripsi ?: '-' }}</div>
                                <code class="small">{{ $jenisSurat->slug }}</code>
                            </td>
                            <td>
                                @if($jenisSurat->is_active)
                                <span class="badge bg-success">Aktif</span>
                                @else
                                <span class="badge bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $jenisSurat->pengajuan_surats_count }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#editJenis{{ $jenisSurat->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('jenis-surat.destroy', $jenisSurat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jenis surat ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr class="collapse" id="editJenis{{ $jenisSurat->id }}">
                            <td colspan="4" class="bg-light">
                                <form action="{{ route('jenis-surat.update', $jenisSurat) }}" method="POST" class="row g-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-4">
                                        <input type="text" name="nama" class="form-control" value="{{ $jenisSurat->nama }}" required>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" name="deskripsi" class="form-control" value="{{ $jenisSurat->deskripsi }}">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-center">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $jenisSurat->is_active ? 'checked' : '' }}>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button class="btn btn-primary btn-sm w-100">Update</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada jenis surat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
