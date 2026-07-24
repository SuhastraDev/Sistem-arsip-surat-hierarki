@extends('layouts.main')

@section('title', 'Input Surat Baru')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient text-white py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="fas fa-envelope-open-text fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="m-0 fw-bold">Formulir Surat Masuk</h5>
                        <small class="opacity-75">Lengkapi informasi surat yang diterima</small>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">

                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-exclamation-circle fa-lg me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <h6 class="alert-heading fw-bold mb-2">Terdapat Kesalahan Input</h6>
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <form action="{{ route('surat-masuk.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-hashtag text-primary"></i>
                            </div>
                            <h6 class="mb-0 fw-bold text-primary">Identitas Surat</h6>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-file-alt text-primary me-2"></i>Nomor Surat
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-barcode text-muted"></i>
                                    </span>
                                    <input type="text" name="nomor_surat" class="form-control border-start-0 ps-0" placeholder="Contoh: 001/SK/XII/2024" required value="{{ old('nomor_surat') }}">
                                </div>
                                <small class="text-muted">Masukkan nomor surat sesuai dokumen asli</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>Tanggal Surat
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="position-relative">
                                    <input type="hidden" name="tanggal_surat" id="tanggalSuratHidden" value="{{ old('tanggal_surat') }}" required>
                                    <div class="input-group date-picker-wrapper" style="cursor: pointer;">
                                        <span class="input-group-text bg-gradient-primary text-white border-0">
                                            <i class="fas fa-calendar-day"></i>
                                        </span>
                                        <input type="text" id="tanggalSuratDisplay" class="form-control border-0 ps-3 date-input" placeholder="DD/MM/YYYY atau klik untuk memilih..." style="background-color: #f8f9fa; cursor: text;">
                                        <span class="input-group-text bg-transparent border-0 pe-3">
                                            <i class="fas fa-chevron-down text-primary"></i>
                                        </span>
                                    </div>
                                    <div class="mt-2 p-3 rounded date-preview" id="datePreview" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-left: 3px solid #667eea; display: none;">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-white rounded shadow-sm p-2 me-3" style="min-width: 60px; text-align: center;">
                                                <div class="text-primary fw-bold" style="font-size: 24px; line-height: 1;" id="dateDay">--</div>
                                                <div class="text-muted small" id="dateMonth">---</div>
                                                <div class="text-muted small" id="dateYear">----</div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="text-muted small mb-1">Tanggal Surat:</div>
                                                <div class="fw-bold text-dark" id="dateFullText">Belum dipilih</div>
                                                <div class="small text-muted" id="dateRelative"></div>
                                            </div>
                                            <div>
                                                <i class="fas fa-check-circle fa-2x text-success"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="dateHelper">
                                        <i class="fas fa-info-circle me-1"></i>Ketik manual (DD/MM/YYYY) atau pilih dari kalender
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-info-circle text-success"></i>
                            </div>
                            <h6 class="mb-0 fw-bold text-success">Detail Surat</h6>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-tag text-success me-2"></i>Kategori Surat
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-list text-muted"></i>
                                </span>
                                <select name="kategori" class="form-select border-start-0 ps-0" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <option value="Laporan" {{ old('kategori') == 'Laporan' ? 'selected' : '' }}>Laporan</option>
                                    <option value="Surat Edaran" {{ old('kategori') == 'Surat Edaran' ? 'selected' : '' }}>Surat Edaran</option>
                                    <option value="Surat Tugas" {{ old('kategori') == 'Surat Tugas' ? 'selected' : '' }}>Surat Tugas</option>
                                    <option value="Undangan" {{ old('kategori') == 'Undangan' ? 'selected' : '' }}>Undangan</option>
                                    <option value="Berita Acara" {{ old('kategori') == 'Berita Acara' ? 'selected' : '' }}>Berita Acara</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-building text-success me-2"></i>Instansi Pengirim
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-university text-muted"></i>
                                </span>
                                <input type="text" name="pengirim" class="form-control border-start-0 ps-0" placeholder="Contoh: Kementerian Lingkungan Hidup dan Kehutanan..." required value="{{ old('pengirim') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                <i class="fas fa-align-left text-success me-2"></i>Perihal Surat
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="perihal" id="perihalSurat" class="form-control" rows="4" placeholder="Tulis ringkasan isi atau perihal surat..." required style="resize: none;">{{ old('perihal') }}</textarea>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted">Jelaskan secara singkat isi atau tujuan surat</small>
                                <small class="text-muted"><span id="charCount">0</span> karakter</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger bg-opacity-10 rounded-circle p-2 me-2">
                                <i class="fas fa-cloud-upload-alt text-danger"></i>
                            </div>
                            <h6 class="mb-0 fw-bold text-danger">Upload Dokumen</h6>
                        </div>

                        <label class="form-label fw-semibold">
                            <i class="fas fa-file-pdf text-danger me-2"></i>File Surat (PDF/DOC/DOCX)
                            <span class="text-danger">*</span>
                        </label>

                        <div class="border-2 border-dashed rounded p-4 text-center position-relative" style="border-color: #dee2e6 !important; background-color: #f8f9fa;">
                            <input type="file" name="file_surat" id="fileSurat" class="form-control d-none" accept=".pdf,.doc,.docx" required>
                            <label for="fileSurat" class="d-block" style="cursor: pointer;">
                                <div id="uploadPlaceholder">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h6 class="fw-bold mb-2">Klik untuk memilih file</h6>
                                    <p class="text-muted mb-0">atau drag & drop file di sini</p>
                                    <small class="text-muted d-block mt-2">
                                        <i class="fas fa-info-circle me-1"></i>Format: PDF, DOC, DOCX | Maksimal: 20MB
                                    </small>
                                </div>
                                <div id="fileInfo" class="d-none">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h6 class="fw-bold mb-1" id="fileName"></h6>
                                    <p class="text-muted mb-0" id="fileSize"></p>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearFile()">
                                        <i class="fas fa-times me-1"></i>Hapus File
                                    </button>
                                </div>
                            </label>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <a href="{{ route('surat-masuk.index') }}" class="btn btn-light border px-4">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-4 py-2 shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i>Simpan & Kirim ke Kabid
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3 bg-light">
            <div class="card-body py-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-lightbulb text-warning fa-lg me-3 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1">Tips Pengisian</h6>
                        <small class="text-muted">
                            Pastikan memilih <b>Kategori Surat</b> yang sesuai agar memudahkan dalam pencarian arsip.
                            Setelah disimpan, surat akan otomatis diteruskan ke Kepala Bidang untuk disposisi lebih lanjut.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/id.js"></script>

<script>
    // Wait for DOM to be fully loaded
    document.addEventListener('DOMContentLoaded', function() {

        // Initialize Flatpickr - Beautiful Custom Date Picker
        const datePicker = flatpickr("#tanggalSuratDisplay", {
            dateFormat: "d/m/Y",
            altInput: true,
            altFormat: "d F Y",
            locale: "id",
            allowInput: true, // Enable manual input
            clickOpens: true,
            disableMobile: true,
            maxDate: "today",
            defaultDate: "{{ old('tanggal_surat') }}",
            // CRITICAL FIX: Enable month and year dropdowns
            monthSelectorType: 'dropdown',
            // This allows proper display of month and year selectors
            static: false,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    // Format for hidden input: Y-m-d
                    const formattedDate = date.getFullYear() + '-' +
                        String(date.getMonth() + 1).padStart(2, '0') + '-' +
                        String(date.getDate()).padStart(2, '0');
                    document.getElementById('tanggalSuratHidden').value = formattedDate;

                    // Update preview
                    updateDatePreview(date);
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // Add custom styling to calendar
                instance.calendarContainer.classList.add('custom-flatpickr');

                // If there's old value, show preview
                if (selectedDates.length > 0) {
                    updateDatePreview(selectedDates[0]);
                }
            }
        });

        // Click on calendar icon to open calendar
        document.querySelector('.date-picker-wrapper .input-group-text:first-child').addEventListener('click', function(e) {
            datePicker.open();
        });

        // Enhanced Date Preview Function
        function updateDatePreview(date) {
            const datePreview = document.getElementById('datePreview');

            if (date) {
                // Get date components
                const day = date.getDate();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const monthFull = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

                const month = monthNames[date.getMonth()];
                const monthName = monthFull[date.getMonth()];
                const year = date.getFullYear();
                const dayName = dayNames[date.getDay()];

                // Calculate relative time
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(date);
                selectedDate.setHours(0, 0, 0, 0);
                const diffTime = selectedDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let relativeText = '';
                if (diffDays === 0) {
                    relativeText = '📍 Hari ini';
                } else if (diffDays === -1) {
                    relativeText = '📅 Kemarin';
                } else if (diffDays === 1) {
                    relativeText = '📅 Besok';
                } else if (diffDays > 1) {
                    relativeText = `📅 ${diffDays} hari dari sekarang`;
                } else if (diffDays < -1 && diffDays > -7) {
                    relativeText = `📅 ${Math.abs(diffDays)} hari yang lalu`;
                } else if (diffDays <= -7 && diffDays > -30) {
                    const weeks = Math.floor(Math.abs(diffDays) / 7);
                    relativeText = `📅 ${weeks} minggu yang lalu`;
                } else if (diffDays <= -30) {
                    const months = Math.floor(Math.abs(diffDays) / 30);
                    relativeText = `📅 ${months} bulan yang lalu`;
                }

                // Update preview elements
                document.getElementById('dateDay').textContent = day;
                document.getElementById('dateMonth').textContent = month;
                document.getElementById('dateYear').textContent = year;
                document.getElementById('dateFullText').textContent = `${dayName}, ${day} ${monthName} ${year}`;
                document.getElementById('dateRelative').textContent = relativeText;

                // Show preview with animation
                datePreview.style.display = 'block';
                datePreview.style.animation = 'slideInUp 0.4s ease-out';
            } else {
                datePreview.style.display = 'none';
            }
        }
    });

    // Character Counter for Perihal
    const perihalInput = document.getElementById('perihalSurat');
    const charCount = document.getElementById('charCount');

    perihalInput.addEventListener('input', function() {
        charCount.textContent = this.value.length;
    });

    // File Upload Enhancement
    const fileInput = document.getElementById('fileSurat');
    const uploadPlaceholder = document.getElementById('uploadPlaceholder');
    const fileInfo = document.getElementById('fileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);

            uploadPlaceholder.classList.add('d-none');
            fileInfo.classList.remove('d-none');
            fileName.textContent = file.name;
            fileSize.textContent = `Ukuran: ${sizeInMB} MB`;
        }
    });

    function clearFile() {
        fileInput.value = '';
        uploadPlaceholder.classList.remove('d-none');
        fileInfo.classList.add('d-none');
    }

    // Drag & Drop functionality
    const dropArea = document.querySelector('.border-dashed');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropArea.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropArea.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropArea.style.borderColor = '#667eea';
        dropArea.style.backgroundColor = '#f0f4ff';
    }

    function unhighlight(e) {
        dropArea.style.borderColor = '#dee2e6';
        dropArea.style.backgroundColor = '#f8f9fa';
    }

    dropArea.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        if (files.length > 0) {
            fileInput.files = files;
            const event = new Event('change', {
                bubbles: true
            });
            fileInput.dispatchEvent(event);
        }
    }
</script>

<style>
    .form-control:focus,
    .input-group:focus-within .input-group-text {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }

    .input-group-text {
        transition: all 0.3s ease;
    }

    .input-group:focus-within .input-group-text {
        background-color: #667eea !important;
        color: white !important;
    }

    .input-group:focus-within .input-group-text i {
        color: white !important;
    }

    .border-dashed {
        transition: all 0.3s ease;
    }

    .border-dashed:hover {
        border-color: #667eea !important;
        background-color: #f0f4ff !important;
    }

    /* Enhanced Date Picker Styles */
    .date-picker-wrapper {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .date-picker-wrapper:hover {
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
        transform: translateY(-1px);
    }

    .date-picker-wrapper:focus-within {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .date-input {
        font-weight: 500;
        color: #2d3748;
    }

    .date-preview {
        animation: slideInUp 0.4s ease-out;
    }

    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom Flatpickr Styling */
    .custom-flatpickr {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border-radius: 8px !important;
        border: none !important;
        font-family: inherit !important;
    }

    .flatpickr-calendar {
        background: white !important;
        border-radius: 8px !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        border: none !important;
        padding: 8px !important;
    }

    .flatpickr-months {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border-radius: 10px 10px 0 0 !important;
        padding: 15px 10px !important;
        margin-bottom: 10px !important;
        position: relative !important;
    }

    .flatpickr-month {
        color: white !important;
        fill: white !important;
        height: auto !important;
    }

    .flatpickr-current-month {
        padding: 5px 0 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: auto !important;
    }

    /* CRITICAL FIX: Ensure month dropdown is visible */
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        appearance: auto !important;
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        background: rgba(255, 255, 255, 0.95) !important;
        color: #667eea !important;
        font-weight: 700 !important;
        border-radius: 8px !important;
        padding: 8px 12px !important;
        border: 2px solid white !important;
        cursor: pointer !important;
        font-size: 14px !important;
        margin-right: 8px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        height: auto !important;
        line-height: normal !important;
        display: inline-block !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months:hover {
        background: white !important;
    }

    /* CRITICAL FIX: Ensure year input is visible and properly sized */
    .flatpickr-current-month .numInputWrapper {
        background: rgba(255, 255, 255, 0.95) !important;
        border-radius: 8px !important;
        border: 2px solid white !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        width: 80px !important;
        height: auto !important;
        display: inline-block !important;
    }

    .flatpickr-current-month .numInputWrapper:hover {
        background: white !important;
    }

    .flatpickr-current-month .numInputWrapper input.cur-year {
        color: #667eea !important;
        font-weight: 700 !important;
        background: transparent !important;
        padding: 8px 6px !important;
        font-size: 14px !important;
        height: auto !important;
        line-height: normal !important;
    }

    .flatpickr-current-month .numInputWrapper span {
        border: none !important;
        opacity: 0.8 !important;
        height: 15px !important;
        width: 100% !important;
    }

    .flatpickr-current-month .numInputWrapper span:hover {
        background: rgba(102, 126, 234, 0.1) !important;
        opacity: 1 !important;
    }

    .flatpickr-current-month .numInputWrapper span.arrowUp {
        top: 0 !important;
    }

    .flatpickr-current-month .numInputWrapper span.arrowDown {
        bottom: 0 !important;
    }

    .flatpickr-current-month .numInputWrapper span.arrowUp:after {
        border-bottom-color: #667eea !important;
    }

    .flatpickr-current-month .numInputWrapper span.arrowDown:after {
        border-top-color: #667eea !important;
    }

    .flatpickr-prev-month,
    .flatpickr-next-month {
        fill: white !important;
        padding: 8px !important;
        border-radius: 6px !important;
        transition: all 0.3s ease !important;
    }

    .flatpickr-prev-month:hover,
    .flatpickr-next-month:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }

    .flatpickr-prev-month svg,
    .flatpickr-next-month svg {
        fill: white !important;
    }

    .flatpickr-weekdays {
        background: #f8f9fa !important;
        border-radius: 8px !important;
        margin: 5px 0 !important;
        padding: 8px 0 !important;
    }

    .flatpickr-weekday {
        color: #667eea !important;
        font-weight: 600 !important;
        font-size: 13px !important;
    }

    .flatpickr-day {
        border-radius: 8px !important;
        font-weight: 500 !important;
        margin: 2px !important;
        border: 2px solid transparent !important;
        transition: all 0.2s ease !important;
    }

    .flatpickr-day:hover {
        background: #f0f4ff !important;
        border-color: #667eea !important;
        color: #667eea !important;
    }

    .flatpickr-day.today {
        border-color: #667eea !important;
        background: #f0f4ff !important;
        color: #667eea !important;
        font-weight: 700 !important;
    }

    .flatpickr-day.selected,
    .flatpickr-day.selected:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        border-color: transparent !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4) !important;
        font-weight: 700 !important;
    }

    .flatpickr-day.prevMonthDay,
    .flatpickr-day.nextMonthDay {
        color: #cbd5e0 !important;
    }

    .flatpickr-day.disabled {
        color: #e2e8f0 !important;
    }

    /* Animation */
    .flatpickr-calendar.open {
        animation: fadeInScale 0.2s ease-out !important;
    }

    @keyframes fadeInScale {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>

@endsection
