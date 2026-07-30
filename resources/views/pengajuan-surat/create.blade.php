@extends('layouts.main')

@section('title', 'Buat Pengajuan Surat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="request-overview mb-4">
            <div class="section-kicker">Pengajuan Baru</div>
            <h4 class="mb-1 fw-bold text-dark">Buat Pengajuan Surat</h4>
            <p class="text-muted mb-0">Pilih jenis surat, lengkapi data sesuai template resmi, lalu sistem menyiapkan preview dan unduhan final.</p>
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
                <strong>Preview dokumen</strong>
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
                            <option value="{{ $jenisSurat->id }}" data-slug="{{ $jenisSurat->slug }}" {{ old('jenis_surat_id') == $jenisSurat->id ? 'selected' : '' }}>
                                {{ $jenisSurat->nama }}
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">Field persyaratan mengikuti file template yang sudah disediakan.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tanggal Pengajuan</label>
                        <input type="date" name="tanggal_pengajuan" class="form-control form-control-lg" value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Perihal</label>
                        <textarea name="perihal" class="form-control" rows="5" maxlength="1000" placeholder="Tuliskan ringkasan kebutuhan surat..." required>{{ old('perihal') }}</textarea>
                    </div>

                    <div class="requirement-panel mb-4" id="requirementPanel">
                        <div class="requirement-panel-empty">
                            <i class="fas fa-list-check"></i>
                            <strong>Pilih jenis surat</strong>
                            <span>Persyaratan khusus akan muncul di sini.</span>
                        </div>
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
@php
$oldFields = old('fields', []);
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const definitions = @json($templateDefinitions);
        const oldFields = @json($oldFields);
        const select = document.querySelector('select[name="jenis_surat_id"]');
        const panel = document.getElementById('requirementPanel');

        function escapeHtml(value) {
            return String(value || '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderFields() {
            const selected = select.options[select.selectedIndex];
            const slug = selected ? selected.dataset.slug : null;
            const definition = slug ? definitions[slug] : null;

            if (!definition) {
                panel.innerHTML = `
                    <div class="requirement-panel-empty">
                        <i class="fas fa-list-check"></i>
                        <strong>Pilih jenis surat</strong>
                        <span>Persyaratan khusus akan muncul di sini.</span>
                    </div>`;
                return;
            }

            const fields = Object.entries(definition.fields).map(([name, field]) => {
                const required = field.required ? 'required' : '';
                const requiredMark = field.required ? '<span class="text-danger">*</span>' : '';
                const value = escapeHtml(oldFields[name] || '');
                const placeholder = escapeHtml(field.placeholder || '');

                if (field.type === 'textarea') {
                    return `
                        <div class="col-12">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <textarea name="fields[${name}]" class="form-control" rows="3" placeholder="${placeholder}" ${required}>${value}</textarea>
                        </div>`;
                }

                if (field.type === 'select') {
                    const options = (field.options || []).map(option => {
                        const safeOption = escapeHtml(option);
                        const selected = value === safeOption ? 'selected' : '';
                        return `<option value="${safeOption}" ${selected}>${safeOption}</option>`;
                    }).join('');

                    return `
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <select name="fields[${name}]" class="form-select" ${required}>
                                <option value="">-- Pilih --</option>
                                ${options}
                            </select>
                        </div>`;
                }

                return `
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                        <input type="${field.type}" name="fields[${name}]" class="form-control" value="${value}" placeholder="${placeholder}" ${required}>
                    </div>`;
            }).join('');

            panel.innerHTML = `
                <div class="requirement-panel-header">
                    <div>
                        <div class="section-kicker">${definition.title}</div>
                        <h6 class="fw-bold mb-1">Persyaratan surat</h6>
                        <p class="text-muted small mb-0">${definition.summary}</p>
                    </div>
                </div>
                <div class="template-source">
                    <div class="template-source-icon"><i class="fas fa-file-word"></i></div>
                    <div>
                        <span>Template digunakan</span>
                        <strong>${escapeHtml(definition.template_label || 'Template sistem')}</strong>
                        <p>${escapeHtml(definition.template_note || 'Data yang diisi akan mengikuti struktur template ini.')}</p>
                    </div>
                </div>
                <div class="row g-3 p-3">${fields}</div>`;
        }

        select.addEventListener('change', renderFields);
        renderFields();
    });
</script>
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

    .requirement-panel {
        background: #f8fafc;
        border: 1px solid #dfe7ef;
        border-radius: 8px;
        overflow: hidden;
    }

    .requirement-panel-header {
        background: #fff;
        border-bottom: 1px solid #e7edf3;
        padding: 16px 18px;
    }

    .requirement-panel-empty {
        align-items: center;
        color: #64748b;
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 34px 20px;
        text-align: center;
    }

    .requirement-panel-empty i {
        color: #0f766e;
        font-size: 2rem;
        opacity: .8;
    }

    .template-source {
        align-items: center;
        background: #f0fdf4;
        border-bottom: 1px solid #d1fae5;
        display: grid;
        gap: 12px;
        grid-template-columns: 42px 1fr;
        padding: 14px 18px;
    }

    .template-source-icon {
        align-items: center;
        background: #fff;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        color: #0f766e;
        display: flex;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .template-source span {
        color: #0f766e;
        display: block;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .template-source strong {
        color: #064e3b;
        display: block;
        font-size: .9rem;
        line-height: 1.25;
    }

    .template-source p {
        color: #475569;
        font-size: .8rem;
        margin: 2px 0 0;
    }

    @media (max-width: 768px) {
        .process-strip {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
