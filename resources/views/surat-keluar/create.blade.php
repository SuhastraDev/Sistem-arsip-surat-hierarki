@extends('layouts.main')

@section('title', 'Buat Surat Keluar')

@section('content')

<!-- Page Header -->
<div class="mb-4">
    <h4 class="mb-1 fw-bold text-dark">Buat Surat Keluar Baru</h4>
    <p class="text-muted mb-0">Upload draft surat untuk divalidasi oleh atasan</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">

        <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-25 rounded-circle p-3">
                            <i class="fas fa-route fa-lg text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="fw-bold mb-2">Alur Proses Surat</h6>
                        <div class="d-flex align-items-center text-muted small">
                            <span class="badge bg-warning text-dark me-2">1</span>
                            <span class="me-2">Anda upload draft</span>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <span class="badge bg-info me-2">2</span>
                            <span class="me-2">Kasi validasi</span>
                            <i class="fas fa-arrow-right mx-2"></i>
                            <span class="badge bg-success me-2">3</span>
                            <span>Surat terbit</span>
                        </div>
                        <div class="mt-2 small">
                            <i class="fas fa-clock me-1 text-warning"></i>
                            Status awal: <strong class="text-warning">Menunggu Validasi</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient text-white py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-25 rounded-circle p-3 me-3">
                        <i class="fas fa-file-edit fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="m-0 fw-bold">Form Konsep Surat</h5>
                        <small class="opacity-75">Lengkapi informasi di bawah ini</small>
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

                <form action="{{ route('surat-keluar.store') }}" method="POST" enctype="multipart/form-data" id="suratForm">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">
                            <span class="badge bg-primary rounded-circle me-2">1</span>
                            Tanggal Surat
                        </label>
                        <div class="position-relative">
                            <input type="hidden" name="tanggal_surat" id="tanggalSuratHidden" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
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

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">
                            <span class="badge bg-primary rounded-circle me-2">2</span>
                            Jenis / Kategori Surat
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-list text-primary"></i>
                            </span>
                            <select name="kategori" class="form-select border-start-0 ps-0 form-select-lg" required style="font-size: 0.95rem;">
                                <option value="" selected disabled>-- Pilih Kategori --</option>
                                <option value="Laporan" {{ old('kategori') == 'Laporan' ? 'selected' : '' }}>Laporan</option>
                                <option value="Surat Edaran" {{ old('kategori') == 'Surat Edaran' ? 'selected' : '' }}>Surat Edaran</option>
                                <option value="Surat Tugas" {{ old('kategori') == 'Surat Tugas' ? 'selected' : '' }}>Surat Tugas</option>
                                <option value="Undangan" {{ old('kategori') == 'Undangan' ? 'selected' : '' }}>Undangan</option>
                                <option value="Berita Acara" {{ old('kategori') == 'Berita Acara' ? 'selected' : '' }}>Berita Acara</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">
                            <span class="badge bg-primary rounded-circle me-2">3</span>
                            Perihal / Judul Surat
                        </label>
                        <textarea name="perihal" id="perihalSurat" class="form-control form-control-lg" rows="4" placeholder="Contoh: Undangan Rapat Koordinasi Reboisasi Tahun 2025" required maxlength="500" style="resize: none;">{{ old('perihal') }}</textarea>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="form-text">
                                <i class="fas fa-lightbulb me-1"></i>
                                Tulis perihal yang jelas dan ringkas
                            </div>
                            <small class="text-muted" id="charCount">0/500</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold mb-2">
                            <span class="badge bg-primary rounded-circle me-2">4</span>
                            Upload Dokumen Draft
                        </label>

                        <div class="border-2 border-dashed rounded-3 p-4 text-center position-relative" style="border-color: #dee2e6; cursor: pointer; transition: all 0.3s;" id="uploadArea">
                            <input type="file" name="file_surat" class="form-control d-none" accept=".pdf,.doc,.docx" required id="fileInput">

                            <label for="fileInput" class="d-block" style="cursor: pointer;">
                                <div id="uploadPrompt">
                                    <div class="mb-3">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-primary opacity-50"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">Klik atau seret file ke sini</h6>
                                    <p class="text-muted small mb-2">
                                        Format: <span class="badge bg-light text-dark">PDF</span>
                                        <span class="badge bg-light text-dark">DOC</span>
                                        <span class="badge bg-light text-dark">DOCX</span>
                                    </p>
                                    <p class="text-muted small mb-0">Maksimal ukuran: 20MB</p>
                                </div>

                                <div id="fileInfo" class="d-none">
                                    <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                    <h6 class="fw-bold mb-1" id="fileName"></h6>
                                    <p class="text-muted mb-0" id="fileSize"></p>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" onclick="clearFile(event)">
                                        <i class="fas fa-times me-1"></i>Hapus File
                                    </button>
                                </div>
                            </label>
                        </div>

                        <div class="form-text mt-2">
                            <i class="fas fa-shield-alt me-1"></i>
                            File Anda aman dan akan disimpan terenkripsi
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex flex-column flex-sm-row gap-3 justify-content-between">
                        <a href="{{ route('surat-keluar.index') }}" class="btn btn-light border btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg fw-bold px-5 shadow-sm" id="submitBtn">
                            <i class="fas fa-paper-plane me-2"></i>
                            Kirim ke Kasi untuk Validasi
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!-- Help Card -->
<div class="card border-0 bg-light mt-3">
    <div class="card-body p-3">
        <div class="d-flex align-items-start">
            <i class="fas fa-lightbulb text-warning fa-lg me-3 mt-1"></i>
            <div>
                <h6 class="fw-bold mb-1">Tips Pengisian</h6>
                <small class="text-muted">
                    Pastikan semua informasi yang diisi sesuai dengan dokumen asli.
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
    document.addEventListener('DOMContentLoaded', function() {

        // Initialize Flatpickr
        const datePicker = flatpickr("#tanggalSuratDisplay", {
            dateFormat: "d/m/Y",
            altInput: true,
            altFormat: "d F Y",
            locale: "id",
            allowInput: true,
            clickOpens: true,
            disableMobile: true,
            maxDate: "today",
            defaultDate: "{{ old('tanggal_surat', date('Y-m-d')) }}",
            monthSelectorType: 'dropdown',
            static: false,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const formattedDate = date.getFullYear() + '-' +
                        String(date.getMonth() + 1).padStart(2, '0') + '-' +
                        String(date.getDate()).padStart(2, '0');
                    document.getElementById('tanggalSuratHidden').value = formattedDate;
                    updateDatePreview(date);
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                instance.calendarContainer.classList.add('custom-flatpickr');
                if (selectedDates.length > 0) {
                    updateDatePreview(selectedDates[0]);
                }
            }
        });

        // Click on calendar icon
        document.querySelector('.date-picker-wrapper .input-group-text:first-child').addEventListener('click', function(e) {
            datePicker.open();
        });

        // Date Preview Function
        function updateDatePreview(date) {
            const datePreview = document.getElementById('datePreview');
            if (date) {
                const day = date.getDate();
                const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const monthFull = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const month = monthNames[date.getMonth()];
                const monthName = monthFull[date.getMonth()];
                const year = date.getFullYear();
                const dayName = dayNames[date.getDay()];

                const today = new Date();
                today.setHours(0, 0, 0, 0);
                const selectedDate = new Date(date);
                selectedDate.setHours(0, 0, 0, 0);
                const diffTime = selectedDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                let relativeText = '';
                if (diffDays === 0) relativeText = '📍 Hari ini';
                else if (diffDays === -1) relativeText = '📅 Kemarin';
                else if (diffDays === 1) relativeText = '📅 Besok';
                else if (diffDays > 1) relativeText = `📅 ${diffDays} hari dari sekarang`;
                else if (diffDays < -1 && diffDays > -7) relativeText = `📅 ${Math.abs(diffDays)} hari yang lalu`;
                else if (diffDays <= -7 && diffDays > -30) {
                    const weeks = Math.floor(Math.abs(diffDays) / 7);
                    relativeText = `📅 ${weeks} minggu yang lalu`;
                } else if (diffDays <= -30) {
                    const months = Math.floor(Math.abs(diffDays) / 30);
                    relativeText = `📅 ${months} bulan yang lalu`;
                }

                document.getElementById('dateDay').textContent = day;
                document.getElementById('dateMonth').textContent = month;
                document.getElementById('dateYear').textContent = year;
                document.getElementById('dateFullText').textContent = `${dayName}, ${day} ${monthName} ${year}`;
                document.getElementById('dateRelative').textContent = relativeText;

                datePreview.style.display = 'block';
                datePreview.style.animation = 'slideInUp 0.4s ease-out';
            } else {
                datePreview.style.display = 'none';
            }
        }

        // Character Counter
        const perihalInput = document.getElementById('perihalSurat');
        const charCount = document.getElementById('charCount');
        perihalInput.addEventListener('input', function() {
            charCount.textContent = `${this.value.length}/500`;
        });

        // File Upload
        const fileInput = document.getElementById('fileInput');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadArea = document.getElementById('uploadArea');

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                if (isValidFile(file)) {
                    showFileInfo(file);
                }
            }
        });

        // Drag & Drop
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = '#667eea';
                uploadArea.style.backgroundColor = '#f0f4ff';
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.style.borderColor = '#dee2e6';
                uploadArea.style.backgroundColor = '';
            }, false);
        });

        uploadArea.addEventListener('drop', function(e) {
            const file = e.dataTransfer.files[0];
            if (file && isValidFile(file)) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                fileInput.files = dataTransfer.files;
                showFileInfo(file);
            }
        }, false);

        function showFileInfo(file) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            uploadPrompt.classList.add('d-none');
            fileInfo.classList.remove('d-none');
            fileName.textContent = file.name;
            fileSize.textContent = `Ukuran: ${sizeInMB} MB`;
        }

        window.clearFile = function(e) {
            e.preventDefault();
            e.stopPropagation();
            fileInput.value = '';
            uploadPrompt.classList.remove('d-none');
            fileInfo.classList.add('d-none');
        }

        function isValidFile(file) {
            const validExtensions = ['pdf', 'doc', 'docx'];
            const extension = file.name.split('.').pop().toLowerCase();
            const maxSize = 2 * 1024 * 1024;

            if (!validExtensions.includes(extension)) {
                alert('Format file tidak valid. Gunakan PDF, DOC, atau DOCX.');
                return false;
            }
            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                return false;
            }
            return true;
        }

        // Form Submit
        const form = document.getElementById('suratForm');
        form.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Mengirim...';
        });
    });
</script>

<style>
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

    /* Flatpickr Custom Styling */
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
    }

    .flatpickr-month {
        color: white !important;
        fill: white !important;
    }

    .flatpickr-current-month .flatpickr-monthDropdown-months {
        appearance: auto !important;
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
    }

    .flatpickr-current-month .numInputWrapper {
        background: rgba(255, 255, 255, 0.95) !important;
        border-radius: 8px !important;
        border: 2px solid white !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        width: 80px !important;
    }

    .flatpickr-current-month .numInputWrapper input.cur-year {
        color: #667eea !important;
        font-weight: 700 !important;
        background: transparent !important;
        padding: 8px 6px !important;
        font-size: 14px !important;
    }

    .flatpickr-prev-month,
    .flatpickr-next-month {
        fill: white !important;
        padding: 8px !important;
        border-radius: 6px !important;
    }

    .flatpickr-prev-month:hover,
    .flatpickr-next-month:hover {
        background: rgba(255, 255, 255, 0.2) !important;
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

    #uploadArea {
        background-color: #f8f9fa;
    }

    #uploadArea:hover {
        border-color: #667eea !important;
        background-color: #f0f4ff !important;
    }
</style>

@endsection
