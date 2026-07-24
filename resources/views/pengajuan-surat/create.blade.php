@extends('layouts.main')

@section('title', 'Buat Pengajuan Surat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="request-overview mb-4">
            <div class="section-kicker">Pengajuan Baru</div>
            <h4 class="mb-1 fw-bold text-dark">Buat Pengajuan Surat</h4>
            <p class="text-muted mb-0">Pilih jenis surat dan tulis kebutuhan awal. Persyaratan rinci akan dibuat pada Fase 2.</p>
        </div>

        <div class="process-strip mb-4">
            <div class="process-step active">
                <span>1</span>
                <strong>Pilih jenis</strong>
            </div>
            <div class="process-step">
                <span>2</span>
                <strong>Menunggu Kasi</strong>
            </div>
            <div class="process-step">
                <span>3</span>
                <strong>Lanjut fase berikutnya</strong>
            </div>
        </div>

        <div class="form-panel">
            <div class="form-panel-header">
                <div>
                    <strong><i class="fas fa-file-edit me-2"></i>Form Pengajuan Awal</strong>
                    <div class="small opacity-75">Data ini menjadi kerangka sebelum form persyaratan detail dibuat.</div>
                </div>
            </div>
            <div class="p-4">
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
                        <button class="btn btn-primary px-4 fw-semibold">
                            <i class="fas fa-paper-plane me-2"></i>Ajukan Surat
                        </button>
                    </div>
                </form>
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

    .request-overview,
    .form-panel,
    .process-strip {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
    }

    .request-overview {
        border-left: 5px solid #0f766e;
        padding: 22px 24px;
    }

    .process-strip {
        display: grid;
        gap: 1px;
        grid-template-columns: repeat(3, 1fr);
        overflow: hidden;
    }

    .process-step {
        align-items: center;
        background: #f8fafc;
        display: flex;
        gap: 10px;
        min-height: 64px;
        padding: 14px 16px;
    }

    .process-step span {
        align-items: center;
        background: #e2e8f0;
        border-radius: 999px;
        color: #334155;
        display: inline-flex;
        font-size: .8rem;
        font-weight: 800;
        height: 28px;
        justify-content: center;
        width: 28px;
    }

    .process-step.active span {
        background: #0f766e;
        color: #fff;
    }

    .form-panel-header {
        background: #0f766e;
        color: #fff;
        padding: 16px 18px;
    }

    @media (max-width: 768px) {
        .process-strip {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
