<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen E-Surat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,600;6..72,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #102033;
            --muted: #647083;
            --paper: #fbfaf6;
            --paper-soft: #f3f0e8;
            --line: #d9ded6;
            --forest: #0f766e;
            --forest-deep: #0b4f49;
            --blueprint: #1d4d7a;
            --clay: #b85c38;
            --gold: #d8a030;
        }

        body {
            background:
                linear-gradient(90deg, rgba(16, 32, 51, .04) 1px, transparent 1px) 0 0 / 34px 34px,
                linear-gradient(180deg, #fbfaf6 0%, #eef4ef 100%);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            min-height: 100vh;
        }

        .verify-shell {
            margin: 0 auto;
            max-width: 1040px;
            padding: 42px 18px;
        }

        .verify-header {
            align-items: flex-start;
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
        }

        .verify-title {
            font-family: 'Newsreader', Georgia, serif;
            font-size: clamp(1.55rem, 4vw, 2.4rem);
            font-weight: 850;
            letter-spacing: 0;
            margin: 0;
        }

        .verify-subtitle {
            color: var(--muted);
            margin: 8px 0 0;
            max-width: 660px;
        }

        .verify-card {
            background: rgba(255, 255, 255, .92);
            border: 1px solid var(--line);
            border-radius: 8px;
            box-shadow: 0 18px 38px rgba(16, 32, 51, .08);
            overflow: hidden;
        }

        .verify-card-header {
            background: linear-gradient(180deg, #fffdf8 0%, #f5f2ea 100%);
            border-bottom: 1px solid var(--line);
            padding: 18px 20px;
        }

        .verify-card-body {
            padding: 22px;
        }

        .result-state {
            align-items: center;
            border-radius: 8px;
            display: flex;
            gap: 14px;
            padding: 16px;
        }

        .result-state i {
            font-size: 1.6rem;
        }

        .result-state.valid {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .result-state.invalid {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .detail-grid {
            display: grid;
            gap: 0;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 16px;
        }

        .detail-item {
            border-bottom: 1px solid #e7ebe4;
            min-height: 82px;
            padding: 14px 0;
        }

        .detail-item:nth-child(odd) {
            padding-right: 16px;
        }

        .detail-item:nth-child(even) {
            border-left: 1px solid #e7ebe4;
            padding-left: 16px;
        }

        .detail-item span {
            color: #64748b;
            display: block;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .detail-item strong,
        .detail-item code {
            color: #0f172a;
            font-size: .88rem;
            word-break: break-word;
        }

        .helper-box {
            background: #f8f5ee;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: #475569;
            font-size: .9rem;
            padding: 14px;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--forest) 0%, var(--forest-deep) 100%);
            border-color: var(--forest);
            font-weight: 800;
        }

        .form-control {
            background: #fffdf8;
            border-color: #cfd8d1;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--forest);
            box-shadow: 0 0 0 .22rem rgba(15, 118, 110, .14);
        }

        @media (max-width: 768px) {
            .verify-header {
                display: block;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .detail-item:nth-child(even) {
                border-left: 0;
                padding-left: 0;
            }

            .detail-item:nth-child(odd) {
                padding-right: 0;
            }
        }
    </style>
</head>

<body>
    <main class="verify-shell">
        <div class="verify-header">
            <div>
                <div class="text-uppercase fw-bold text-success small mb-2">E-Surat Suhastra</div>
                <h1 class="verify-title">Verifikasi Dokumen</h1>
                <p class="verify-subtitle">Masukkan kode verifikasi dari dokumen bertanda tangan digital. Upload PDF/DOCX bersifat opsional untuk memastikan file yang dipegang sama dengan arsip final.</p>
            </div>
            @auth
            <a href="{{ route('dashboard') }}" class="btn btn-light border mt-3 mt-md-0">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
            @endauth
        </div>

        <div class="row g-3">
            <div class="col-lg-5">
                <div class="verify-card">
                    <div class="verify-card-header">
                        <strong><i class="fas fa-shield-halved me-2 text-success"></i>Cek Keaslian</strong>
                    </div>
                    <div class="verify-card-body">
                        @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif
                        <form action="{{ route('verification.verify') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Kode verifikasi</label>
                                <input type="text" name="kode" class="form-control form-control-lg" value="{{ old('kode', $result['code'] ?? '') }}" placeholder="Contoh: ES-A1B2C3D4E5" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Upload dokumen final</label>
                                <input type="file" name="dokumen" class="form-control" accept=".pdf,.docx,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                <div class="form-text">Gunakan file PDF atau DOCX yang diunduh setelah dokumen ditandatangani.</div>
                            </div>
                            <button class="btn btn-success w-100">
                                <i class="fas fa-magnifying-glass me-1"></i>Verifikasi
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="verify-card h-100">
                    <div class="verify-card-header">
                        <strong><i class="fas fa-clipboard-check me-2 text-success"></i>Hasil Verifikasi</strong>
                    </div>
                    <div class="verify-card-body">
                        @if($result)
                        <div class="result-state {{ $result['status'] }}">
                            <i class="fas {{ $result['status'] === 'valid' ? 'fa-circle-check' : 'fa-triangle-exclamation' }}"></i>
                            <div>
                                <h5 class="mb-1">{{ $result['title'] }}</h5>
                                <div>{{ $result['message'] }}</div>
                            </div>
                        </div>

                        @if($result['signature'])
                        @php($signature = $result['signature'])
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span>Nomor Pengajuan</span>
                                <strong>{{ $signature->pengajuanSurat->nomor_pengajuan }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Jenis Surat</span>
                                <strong>{{ $signature->pengajuanSurat->jenisSurat->nama }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Pemohon</span>
                                <strong>{{ $signature->pengajuanSurat->pemohon->name }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Penandatangan</span>
                                <strong>{{ $signature->signer->name }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Waktu Tanda Tangan</span>
                                <strong>{{ $signature->signed_at->format('d/m/Y H:i') }} WIB</strong>
                            </div>
                            <div class="detail-item">
                                <span>Algoritma</span>
                                <strong>{{ $signature->algorithm }}</strong>
                            </div>
                            <div class="detail-item">
                                <span>Kode</span>
                                <code>{{ $signature->verification_code }}</code>
                            </div>
                            <div class="detail-item">
                                <span>Hash Dokumen</span>
                                <code>{{ $signature->document_hash }}</code>
                            </div>
                        </div>
                        @endif

                        @if($result['file'])
                        <div class="helper-box mt-3">
                            <strong>File upload:</strong> {{ $result['file']['name'] }}<br>
                            <strong>Status file:</strong> {{ $result['file']['matched'] ? 'Hash file cocok dengan arsip final.' : 'Hash file tidak cocok dengan arsip final.' }}
                        </div>
                        @endif
                        @else
                        <div class="helper-box">
                            Hasil akan muncul setelah kode verifikasi dicek atau QR dari dokumen dipindai.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
