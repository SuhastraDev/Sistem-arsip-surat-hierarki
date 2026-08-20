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

                <form action="{{ route('pengajuan-surat.store') }}" method="POST" enctype="multipart/form-data">
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
                        <input type="date" name="tanggal_pengajuan" class="form-control form-control-lg" value="{{ old('tanggal_pengajuan', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
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
                        <button class="btn btn-primary px-4 fw-semibold" id="submitPengajuanButton">
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
        const pegawaiProfile = @json($pegawaiProfile);
        const cutiUsage = @json($cutiUsage);
        const select = document.querySelector('select[name="jenis_surat_id"]');
        const panel = document.getElementById('requirementPanel');
        const submitButton = document.getElementById('submitPengajuanButton');
        const today = new Date();
        const todayValue = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
        const systemValues = {
            'user.name': pegawaiProfile.name,
            'user.nip': pegawaiProfile.nip,
            'user.jabatan': pegawaiProfile.jabatan,
            'user.atasan_langsung': pegawaiProfile.atasan_langsung || '',
            'nota.kepada': 'Kepala Dinas Kehutanan Provinsi Sumatera Selatan',
            'nota.kabid': pegawaiProfile.kabid_penandatangan || '',
            'nota.kabid_nama': pegawaiProfile.kabid_nama || '',
            'nota.kabid_jabatan': pegawaiProfile.kabid_jabatan || '',
            'nota.kabid_nip': pegawaiProfile.kabid_nip || '',
            'nota.kabid_pangkat': pegawaiProfile.kabid_pangkat || '',
            'surat_tugas.nomor_surat': pegawaiProfile.surat_tugas_nomor || '',
            'surat_tugas.dasar_pertama': pegawaiProfile.surat_tugas_dasar_pertama || '',
            'surat_tugas.dasar_kedua': pegawaiProfile.surat_tugas_dasar_kedua || '',
        };

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
            submitButton.disabled = false;

            if (!definition) {
                submitButton.disabled = false;
                panel.innerHTML = `
                    <div class="requirement-panel-empty">
                        <i class="fas fa-list-check"></i>
                        <strong>Pilih jenis surat</strong>
                        <span>Persyaratan khusus akan muncul di sini.</span>
                    </div>`;
                return;
            }

            let activeGroup = null;
            const fields = Object.entries(definition.fields).map(([name, field]) => {
                const required = field.required ? 'required' : '';
                const requiredMark = field.required ? '<span class="text-danger">*</span>' : '';
                const systemValue = field.source ? (systemValues[field.source] || '') : '';
                const defaultValue = field.default || '';
                const value = escapeHtml(oldFields[name] || systemValue || defaultValue || '');
                const placeholder = escapeHtml(field.placeholder || '');
                const readonly = field.readonly ? 'readonly' : '';
                const helper = field.readonly ? '<div class="form-text">Terisi otomatis dari data akun pegawai.</div>' : '';
                const groupHeader = field.group && field.group !== activeGroup
                    ? `<div class="col-12 field-group-divider"><span>${escapeHtml(field.group)}</span></div>`
                    : '';
                activeGroup = field.group || activeGroup;

                if (field.type === 'file') {
                    const accept = escapeHtml(field.accept || '');

                    return `${groupHeader}
                        <div class="col-12">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <input type="file" name="fields[${name}]" class="form-control" accept="${accept}" ${required}>
                            <div class="form-text">${placeholder || 'Upload dokumen pendukung jika diperlukan.'} Maksimal 5 MB.</div>
                        </div>`;
                }

                if (field.type === 'textarea') {
                    return `${groupHeader}
                        <div class="col-12">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <textarea name="fields[${name}]" class="form-control" rows="3" placeholder="${placeholder}" ${required} ${readonly}>${value}</textarea>
                            ${helper}
                        </div>`;
                }

                if (field.type === 'people') {
                    return `${groupHeader}
                        <div class="col-12">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <div class="people-repeater" data-name="${name}" data-item-fields='${JSON.stringify(field.item_fields || {})}'></div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1 add-person-btn" data-target="${name}">
                                <i class="fas fa-plus me-1"></i>Tambah Orang
                            </button>
                        </div>`;
                }

                if (field.type === 'select') {
                    const options = (field.options || []).map(option => {
                        const safeOption = escapeHtml(option);
                        const selected = value === safeOption ? 'selected' : '';
                        return `<option value="${safeOption}" ${selected}>${safeOption}</option>`;
                    }).join('');

                    return `${groupHeader}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                            <select name="fields[${name}]" class="form-select" ${required}>
                                <option value="">-- Pilih --</option>
                                ${options}
                            </select>
                        </div>`;
                }

                return `${groupHeader}
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">${field.label} ${requiredMark}</label>
                        <input type="${field.type}" name="fields[${name}]" class="form-control" value="${value}" placeholder="${placeholder}" ${field.type === 'date' ? `min="${todayValue}"` : ''} ${required} ${readonly}>
                        ${helper}
                    </div>`;
            }).join('');

            const quotaPanel = slug === 'surat-cuti' ? `
                <div class="quota-panel mx-3 mt-3" id="cutiQuotaPanel">
                    <div>
                        <span id="cutiQuotaTitle">Kuota cuti</span>
                        <strong id="cutiQuotaUsage">Pilih jenis cuti</strong>
                    </div>
                    <div class="quota-result" id="cutiQuotaResult">Pilih tanggal cuti untuk melihat sisa kuota.</div>
                </div>` : '';
            const travelPanel = slug === 'surat-tugas' ? `
                <div class="quota-panel mx-3 mt-3" id="suratTugasTravelPanel">
                    <div>
                        <span>Durasi Perjalanan</span>
                        <strong id="suratTugasTravelDays">Belum dihitung</strong>
                    </div>
                    <div class="quota-result" id="suratTugasTravelResult">Pilih tanggal mulai dan selesai perjalanan.</div>
                </div>` : '';

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
                ${quotaPanel}
                ${travelPanel}
                <div class="row g-3 p-3">${fields}</div>`;

            if (slug === 'surat-cuti') {
                bindCutiCalculator();
            }

            if (slug === 'surat-tugas') {
                bindSuratTugasCalculator();
            }

            panel.querySelectorAll('.people-repeater').forEach(initPeopleRepeater);
        }

        function initPeopleRepeater(container) {
            const name = container.dataset.name;
            const itemFields = JSON.parse(container.dataset.itemFields || '{}');
            const existing = Array.isArray(oldFields[name]) ? oldFields[name] : null;
            const rows = (existing && existing.length ? existing : [{}]);

            rows.forEach(row => addPersonRow(container, name, itemFields, row));
        }

        function addPersonRow(container, name, itemFields, values) {
            values = values || {};
            const index = container.children.length;
            const row = document.createElement('div');
            row.className = 'person-row border rounded p-3 mb-2';

            const inputs = Object.entries(itemFields).map(([itemKey, itemField]) => `
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-1">${escapeHtml(itemField.label || itemKey)}</label>
                    <input type="text" name="fields[${name}][${index}][${itemKey}]" class="form-control form-control-sm" value="${escapeHtml(values[itemKey] || '')}" placeholder="${escapeHtml(itemField.placeholder || '')}">
                </div>`).join('');

            row.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="small text-uppercase text-muted person-row-title">Orang ke-${index + 1}</strong>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-person-btn"><i class="fas fa-trash"></i></button>
                </div>
                <div class="row g-2">${inputs}</div>`;

            container.appendChild(row);
        }

        function renumberPersonRows(container, name) {
            Array.from(container.children).forEach((row, index) => {
                row.querySelector('.person-row-title').textContent = `Orang ke-${index + 1}`;
                row.querySelectorAll('input[name]').forEach(input => {
                    input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
                });
            });
        }

        panel.addEventListener('click', function(event) {
            const addBtn = event.target.closest('.add-person-btn');

            if (addBtn) {
                const container = panel.querySelector(`.people-repeater[data-name="${addBtn.dataset.target}"]`);
                const itemFields = JSON.parse(container.dataset.itemFields || '{}');
                addPersonRow(container, addBtn.dataset.target, itemFields, {});
                return;
            }

            const removeBtn = event.target.closest('.remove-person-btn');

            if (removeBtn) {
                const container = removeBtn.closest('.people-repeater');

                if (container.children.length <= 1) {
                    return;
                }

                removeBtn.closest('.person-row').remove();
                renumberPersonRows(container, container.dataset.name);
            }
        });

        function bindCutiCalculator() {
            const startInput = panel.querySelector('[name="fields[tanggal_mulai]"]');
            const endInput = panel.querySelector('[name="fields[tanggal_selesai]"]');
            const jenisInput = panel.querySelector('[name="fields[jenis_cuti]"]');
            const lamaInput = panel.querySelector('[name="fields[lama_cuti]"]');
            const result = document.getElementById('cutiQuotaResult');
            const quotaTitle = document.getElementById('cutiQuotaTitle');
            const quotaUsage = document.getElementById('cutiQuotaUsage');

            function daysBetween(start, end) {
                if (!start || !end) return 0;
                const startDate = new Date(`${start}T00:00:00`);
                const endDate = new Date(`${end}T00:00:00`);
                if (endDate < startDate) return 0;
                return Math.floor((endDate - startDate) / 86400000) + 1;
            }

            function updateQuota() {
                const requested = daysBetween(startInput.value, endInput.value);
                const startYear = startInput.value ? new Date(`${startInput.value}T00:00:00`).getFullYear() : cutiUsage.year;
                const leaveType = jenisInput.value || 'Cuti tahunan';
                const quotaDefinition = (cutiUsage.types || {})[leaveType] || {};
                const quotaDays = quotaDefinition.quota_days;
                const used = Number((((cutiUsage.usage || {})[leaveType] || {})[startYear]) || 0);
                const remaining = quotaDays === null || quotaDays === undefined ? null : quotaDays - used;
                quotaTitle.textContent = `Kuota ${leaveType || 'cuti'}`;
                quotaUsage.textContent = quotaDays === null || quotaDays === undefined
                    ? 'Tidak mengurangi kuota'
                    : `${used} hari terpakai / ${quotaDefinition.label || `${quotaDays} hari`}`;
                submitButton.disabled = false;

                if (!requested) {
                    lamaInput.value = '';
                    result.className = 'quota-result';
                    result.textContent = 'Pilih tanggal cuti untuk melihat sisa kuota.';
                    return;
                }

                lamaInput.value = `${requested} hari`;

                if (quotaDays === null || quotaDays === undefined) {
                    result.className = 'quota-result';
                    result.textContent = `${leaveType} tidak dihitung dan tidak mengurangi kuota.`;
                    return;
                }

                if (requested > remaining || requested > quotaDays) {
                    result.className = 'quota-result danger';
                    result.textContent = `Melebihi kuota ${leaveType} tahun ${startYear}. Sisa ${Math.max(remaining, 0)} hari, pengajuan ini ${requested} hari.`;
                    submitButton.disabled = true;
                    return;
                }

                result.className = 'quota-result success';
                result.textContent = `Aman. Setelah pengajuan ini sisa kuota ${remaining - requested} hari.`;
            }

            startInput.addEventListener('change', updateQuota);
            endInput.addEventListener('change', updateQuota);
            jenisInput.addEventListener('change', updateQuota);
            updateQuota();
        }

        function bindSuratTugasCalculator() {
            const startInput = panel.querySelector('[name="fields[tanggal_mulai_perjalanan]"]');
            const endInput = panel.querySelector('[name="fields[tanggal_selesai_perjalanan]"]');
            const lamaInput = panel.querySelector('[name="fields[lama_perjalanan]"]');
            const daysText = document.getElementById('suratTugasTravelDays');
            const result = document.getElementById('suratTugasTravelResult');

            function daysBetween(start, end) {
                if (!start || !end) return 0;
                const startDate = new Date(`${start}T00:00:00`);
                const endDate = new Date(`${end}T00:00:00`);
                if (endDate < startDate) return 0;
                return Math.floor((endDate - startDate) / 86400000) + 1;
            }

            function formatDate(value) {
                if (!value) return '-';
                const [year, month, day] = value.split('-');
                return `${day}/${month}/${year}`;
            }

            function updateTravelDuration() {
                const requested = daysBetween(startInput.value, endInput.value);
                submitButton.disabled = false;

                if (!startInput.value || !endInput.value) {
                    lamaInput.value = '';
                    daysText.textContent = 'Belum dihitung';
                    result.className = 'quota-result';
                    result.textContent = 'Pilih tanggal mulai dan selesai perjalanan.';
                    return;
                }

                if (!requested) {
                    lamaInput.value = '';
                    daysText.textContent = 'Tanggal belum valid';
                    result.className = 'quota-result danger';
                    result.textContent = 'Tanggal selesai harus sama atau setelah tanggal mulai.';
                    submitButton.disabled = true;
                    return;
                }

                lamaInput.value = `${requested} hari / ${formatDate(startInput.value)} s.d. ${formatDate(endInput.value)}`;
                daysText.textContent = `${requested} hari`;
                result.className = 'quota-result success';
                result.textContent = 'Tidak ada kuota perjalanan, durasi hanya dihitung dari tanggal.';
            }

            startInput.addEventListener('change', updateTravelDuration);
            endInput.addEventListener('change', updateTravelDuration);
            updateTravelDuration();
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

    .field-group-divider {
        align-items: center;
        color: #0f766e;
        display: flex;
        font-size: .72rem;
        font-weight: 900;
        gap: 10px;
        letter-spacing: .08em;
        margin-top: 4px;
        text-transform: uppercase;
    }

    .field-group-divider::after {
        background: #cbd5e1;
        content: "";
        flex: 1;
        height: 1px;
    }

    .quota-panel {
        align-items: center;
        background: #fffdf8;
        border: 1px solid #f1dca7;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 12px 14px;
    }

    .quota-panel span {
        color: #92400e;
        display: block;
        font-size: .68rem;
        font-weight: 900;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    .quota-panel strong {
        color: #102033;
        display: block;
        font-size: .92rem;
    }

    .quota-result {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        color: #64748b;
        font-size: .8rem;
        font-weight: 800;
        padding: 7px 11px;
        text-align: right;
    }

    .quota-result.success {
        background: #ecfdf5;
        border-color: #bbf7d0;
        color: #166534;
    }

    .quota-result.danger {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .process-strip {
            grid-template-columns: 1fr;
        }

        .quota-panel {
            align-items: flex-start;
            flex-direction: column;
        }

        .quota-result {
            border-radius: 8px;
            text-align: left;
        }
    }
</style>
@endsection
