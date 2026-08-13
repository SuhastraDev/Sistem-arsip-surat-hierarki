@extends('layouts.main')

@section('title', 'Tambah Pegawai')

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-9">

        <!-- Header Section -->
        <div class="content-header bg-white p-3 rounded shadow-sm mb-3">
            <div class="d-flex align-items-center">
                <a href="{{ route('users.index') }}" class="btn btn-light btn-sm me-3">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h5 class="mb-1">
                        <i class="fas fa-user-plus me-2 text-primary"></i>Form Registrasi Pegawai
                    </h5>
                    <small class="text-muted">Tambahkan akun pegawai baru ke sistem</small>
                </div>
            </div>
        </div>

        <!-- Error Alert -->
        @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-3" role="alert">
            <div class="d-flex align-items-start">
                <i class="fas fa-exclamation-circle me-3 mt-1"></i>
                <div>
                    <strong>Terdapat kesalahan:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <!-- Form Section -->
        <div class="form-container bg-white rounded shadow-sm">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <!-- Section 1: Info Pribadi -->
                <div class="form-section">
                    <div class="section-header">
                        <h6 class="mb-0">
                            <span class="section-number">1</span>
                            Informasi Akun & Pribadi
                        </h6>
                    </div>
                    <div class="section-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control"
                                    placeholder="Contoh: Budi Santoso, S.Hut"
                                    value="{{ old('name') }}" required>
                                <small class="text-muted">Nama lengkap beserta gelar</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">
                                    NIP <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nip" class="form-control"
                                    placeholder="Contoh: 198705122010011002"
                                    value="{{ old('nip') }}" required>
                                <small class="text-muted">Dipakai untuk login dan otomatis terisi di form cuti pegawai</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Jabatan Dinas <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="jabatan" class="form-control"
                                    placeholder="Contoh: Staf Administrasi Hutan"
                                    value="{{ old('jabatan') }}" required>
                                <small class="text-muted">Jabatan resmi di dinas</small>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Password Default <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="text" name="password" class="form-control bg-light"
                                        value="password123" readonly>
                                </div>
                                <small class="text-warning">
                                    <i class="fas fa-info-circle me-1"></i>Password awal: <strong>password123</strong>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Hak Akses -->
                <div class="form-section">
                    <div class="section-header">
                        <h6 class="mb-0">
                            <span class="section-number">2</span>
                            Hak Akses & Hierarki
                        </h6>
                    </div>
                    <div class="section-body">
                        <div class="mb-4">
                            <label class="form-label">
                                Role (Hak Akses) <span class="text-danger">*</span>
                            </label>
                            <select name="role" id="roleSelect" class="form-select" onchange="cekAtasan()" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="kabid" {{ old('role') == 'kabid' ? 'selected' : '' }}>
                                    Kepala Bidang (Kabid)
                                </option>
                                <option value="kasi" {{ old('role') == 'kasi' ? 'selected' : '' }}>
                                    Kepala Seksi (Kasi)
                                </option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>
                                    Staf Pelaksana
                                </option>
                            </select>
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i>Menentukan hak akses dalam sistem
                            </small>
                        </div>

                        <!-- Atasan Section -->
                        <div id="atasanSection" class="atasan-box" style="display: none;">
                            <div class="d-flex align-items-start mb-3">
                                <div class="atasan-icon">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <div class="flex-fill">
                                    <label class="form-label fw-semibold mb-2" id="labelAtasan">
                                        Pilih Atasan Langsung:
                                    </label>

                                    <!-- Dropdown Kabid (untuk Kasi) -->
                                    <div id="wrapperKabid" style="display: none;">
                                        <select name="parent_id" id="selectKabid" class="form-select">
                                            <option value="">-- Pilih Kabid --</option>
                                            @foreach($kabids as $k)
                                            <option value="{{ $k->id }}" {{ old('parent_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->name }} ({{ $k->jabatan }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Dropdown Kasi (untuk Staff) -->
                                    <div id="wrapperKasi" style="display: none;">
                                        <select name="parent_id" id="selectKasi" class="form-select">
                                            <option value="">-- Pilih Kasi --</option>
                                            @foreach($kasis as $k)
                                            <option value="{{ $k->id }}" {{ old('parent_id') == $k->id ? 'selected' : '' }}>
                                                {{ $k->name }} ({{ $k->jabatan }})
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pegawai ini akan mengirim disposisi/surat kepada atasan yang dipilih.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-actions">
                    <a href="{{ route('users.index') }}" class="btn btn-light border">
                        <i class="fas fa-times me-2"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan Data Pegawai
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<style>
    /* Content Header */
    .content-header {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .content-header h5 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    /* Form Container */
    .form-container {
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    /* Form Sections */
    .form-section {
        border-bottom: 1px solid #e9ecef;
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .section-header {
        padding: 1rem 1.5rem;
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
    }

    .section-header h6 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #495057;
        display: flex;
        align-items: center;
    }

    .section-number {
        width: 28px;
        height: 28px;
        background: #004085;
        color: white;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.813rem;
        font-weight: 600;
        margin-right: 0.75rem;
    }

    .section-body {
        padding: 1.5rem;
    }

    /* Form Elements */
    .form-label {
        font-size: 0.813rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 0.5rem;
    }

    .form-control,
    .form-select {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
        border: 1px solid #ced4da;
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #004085;
        box-shadow: 0 0 0 0.15rem rgba(0, 64, 133, 0.15);
    }

    .input-group-text {
        background: #f8f9fa;
        border: 1px solid #ced4da;
        color: #6c757d;
        font-size: 0.875rem;
    }

    /* Atasan Box */
    .atasan-box {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #004085;
        border-radius: 8px;
        padding: 1.25rem;
        position: relative;
    }

    .atasan-icon {
        width: 40px;
        height: 40px;
        background: #004085;
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        font-size: 1.125rem;
        flex-shrink: 0;
    }

    /* Form Actions */
    .form-actions {
        padding: 1.5rem;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    /* Buttons */
    .btn {
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .btn-primary {
        background: linear-gradient(135deg, #004085 0%, #0056b3 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #003266 0%, #004085 100%);
    }

    /* Alert */
    .alert {
        border-radius: 6px;
    }

    /* Text Helpers */
    small.text-muted {
        font-size: 0.75rem;
        display: block;
        margin-top: 0.25rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .section-body {
            padding: 1rem;
        }

        .form-actions {
            padding: 1rem;
            flex-direction: column;
        }

        .form-actions .btn {
            width: 100%;
        }
    }
</style>

<script>
    // Auto-trigger on page load if old input exists
    document.addEventListener('DOMContentLoaded', function() {
        var role = document.getElementById("roleSelect").value;
        if (role) {
            cekAtasan();
        }
    });

    function cekAtasan() {
        var role = document.getElementById("roleSelect").value;
        var section = document.getElementById("atasanSection");
        var wrapperKabid = document.getElementById("wrapperKabid");
        var wrapperKasi = document.getElementById("wrapperKasi");
        var selectKabid = document.getElementById("selectKabid");
        var selectKasi = document.getElementById("selectKasi");

        // 1. Reset Tampilan
        section.style.display = "none";
        wrapperKabid.style.display = "none";
        wrapperKasi.style.display = "none";

        // 2. Disable fields
        selectKabid.disabled = true;
        selectKasi.disabled = true;

        // 3. Logika Role
        if (role === 'kasi') {
            section.style.display = "block";
            wrapperKabid.style.display = "block";
            selectKabid.disabled = false;
            document.getElementById("labelAtasan").innerText = "Siapa Kabid (Atasan) Kasi ini?";
        } else if (role === 'staff') {
            section.style.display = "block";
            wrapperKasi.style.display = "block";
            selectKasi.disabled = false;
            document.getElementById("labelAtasan").innerText = "Siapa Kasi (Atasan) Staf ini?";
        }
    }
</script>

@endsection
