@extends('layouts.main')

@section('title', 'Buat Pengajuan Surat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <h4 class="mb-1 fw-bold text-dark">Buat Pengajuan Surat</h4>
            <p class="text-muted mb-0">Fase 1 menyiapkan struktur awal. Form detail per jenis surat akan dibuat pada Fase 2.</p>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                    <span class="badge bg-primary">1</span><span>Pilih jenis surat</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="badge bg-warning text-dark">2</span><span>Masuk antrean Kasi/Kabid</span>
                    <i class="fas fa-arrow-right"></i>
                    <span class="badge bg-success">3</span><span>Template, approval, dan tanda tangan menyusul di fase berikutnya</span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <strong><i class="fas fa-file-edit me-2"></i>Form Pengajuan Awal</strong>
            </div>
            <div class="card-body p-4">
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

                <form action="{{ route('pengajuan-surat.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Jenis Surat</label>
                        <select name="jenis_surat_id" class="form-select form-select-lg" required>
                            <option value="" selected disabled>-- Pilih Jenis Surat --</option>
                            @foreach($jenisSurats as $jenisSurat)
                            <option value="{{ $jenisSurat->id }}" {{ old('jenis_surat_id') == $jenisSurat->id ? 'selected' : '' }}>
                                {{ $jenisSurat->nama }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Pilihan awal: Surat Cuti, Surat Tugas, dan Nota Dinas.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tanggal Pengajuan</label>
                        <input type="date" name="tanggal_pengajuan" class="form-control form-control-lg" value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Perihal</label>
                        <textarea name="perihal" class="form-control" rows="5" maxlength="1000" placeholder="Tuliskan ringkasan kebutuhan surat..." required>{{ old('perihal') }}</textarea>
                    </div>

                    <div class="d-flex justify-content-between gap-3">
                        <a href="{{ route('pengajuan-surat.index') }}" class="btn btn-light border">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button class="btn btn-primary px-4">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Surat
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
