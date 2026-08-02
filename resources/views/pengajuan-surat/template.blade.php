<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pengajuanSurat->jenisSurat->nama }} - {{ $pengajuanSurat->nomor_pengajuan }}</title>
    <style>
        body {
            background: #edf2f7;
            color: #111827;
            font-family: Arial, sans-serif;
            line-height: 1.55;
            margin: 0;
            padding: 28px;
        }

        .document-page {
            background: #fff;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
            margin: 0 auto;
            max-width: 794px;
            min-height: 1123px;
            padding: 54px 60px;
        }

        .letterhead {
            border-bottom: 3px double #0f766e;
            margin-bottom: 28px;
            padding-bottom: 18px;
            text-align: center;
        }

        .letterhead h1 {
            font-size: 18px;
            letter-spacing: .08em;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .letterhead p {
            color: #475569;
            font-size: 12px;
            margin: 0;
        }

        .title {
            margin-bottom: 24px;
            text-align: center;
        }

        .title h2 {
            font-size: 17px;
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .title div {
            color: #475569;
            font-size: 12px;
            margin-top: 4px;
        }

        .template-origin {
            background: #f8fafc;
            border: 1px solid #dbe5ef;
            color: #334155;
            font-size: 12px;
            margin: -8px 0 22px;
            padding: 10px 12px;
        }

        .template-origin strong {
            color: #0f766e;
        }

        .meta,
        .requirements {
            border-collapse: collapse;
            margin-bottom: 24px;
            width: 100%;
        }

        .meta td,
        .requirements td {
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
            padding: 9px 8px;
            vertical-align: top;
        }

        .meta td:first-child,
        .requirements td:first-child {
            color: #475569;
            font-weight: bold;
            width: 34%;
        }

        .body-copy {
            font-size: 13.5px;
            margin-bottom: 24px;
            text-align: justify;
        }

        .signature-space {
            display: flex;
            justify-content: flex-end;
            margin-top: 58px;
        }

        .signature-box {
            text-align: center;
            width: 260px;
        }

        .signature-line {
            align-items: center;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 132px;
            margin: 8px 0;
            padding: 10px;
        }

        .signature-line.unsigned {
            background: #fff;
            border: 0;
            border-bottom: 1px solid #111827;
            min-height: 76px;
            padding: 0;
        }

        .signature-line img {
            border: 1px solid #d1fae5;
            display: block;
            height: 86px;
            width: 86px;
        }

        .signature-line code {
            background: #fff;
            border: 1px solid #bbf7d0;
            color: #166534;
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            margin-top: 6px;
            padding: 3px 6px;
        }

        .signature-note {
            color: #166534;
            font-size: 10.5px;
            font-weight: bold;
            margin-top: 4px;
        }

        .toolbar {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: space-between;
            margin: 0 auto 18px;
            max-width: 794px;
        }

        .toolbar-title {
            color: #334155;
            font-size: 13px;
            font-weight: 700;
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .toolbar a,
        .toolbar button {
            background: #0f766e;
            border: 0;
            border-radius: 7px;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            font-weight: 700;
            padding: 10px 14px;
            text-decoration: none;
        }

        .toolbar .secondary {
            background: #fff;
            border: 1px solid #cbd5e1;
            color: #334155;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .toolbar {
                display: none;
            }

            .document-page {
                box-shadow: none;
                margin: 0;
                max-width: none;
                min-height: auto;
                padding: 36px 44px;
            }
        }

        body.is-embed {
            background: #e2e8f0;
            padding: 14px;
        }

        body.is-embed .document-page {
            box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
            min-height: 980px;
            padding: 44px 52px;
        }
    </style>
</head>

<body class="{{ ($isEmbed ?? false) ? 'is-embed' : '' }}">
    @unless($isPrint || ($isEmbed ?? false))
    <div class="toolbar">
        <div class="toolbar-title">Preview dokumen sebelum diunduh</div>
        <div class="toolbar-actions">
            <a href="{{ route('pengajuan-surat.show', $pengajuanSurat) }}" class="secondary">Kembali</a>
            <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'pdf']) }}">Download PDF</a>
            <a href="{{ route('pengajuan-surat.export', [$pengajuanSurat, 'docx']) }}">Download DOCX</a>
            <button type="button" class="secondary" onclick="window.print()">Cetak dari browser</button>
        </div>
    </div>
    @endunless

    <main class="document-page">
        <header class="letterhead">
            <h1>Dinas Kehutanan</h1>
            <p>Sistem E-Arsip Surat Digital</p>
        </header>

        <section class="title">
            <h2>{{ $pengajuanSurat->jenisSurat->nama }}</h2>
            <div>Nomor pengajuan: {{ $pengajuanSurat->nomor_pengajuan }}</div>
        </section>

        <div class="template-origin">
            Template sumber: <strong>{{ $templateDefinition['template_label'] ?? ($pengajuanSurat->metadata['template_source'] ?? 'Template sistem') }}</strong>
        </div>

        <table class="meta">
            <tr>
                <td>Tanggal pengajuan</td>
                <td>{{ $pengajuanSurat->tanggal_pengajuan->format('d F Y') }}</td>
            </tr>
            <tr>
                <td>Pemohon</td>
                <td>{{ $pengajuanSurat->pemohon->name }} - {{ $pengajuanSurat->pemohon->jabatan }}</td>
            </tr>
            <tr>
                <td>Perihal</td>
                <td>{{ $pengajuanSurat->perihal }}</td>
            </tr>
        </table>

        <p class="body-copy">
            Berdasarkan data pengajuan yang telah diisi pada sistem, berikut ringkasan persyaratan
            dan informasi utama untuk {{ strtolower($pengajuanSurat->jenisSurat->nama) }}.
        </p>

        <table class="requirements">
            @foreach($rows as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['value'] }}</td>
            </tr>
            @endforeach
        </table>

        @if(! $pengajuanSurat->digitalSignature)
        <p class="body-copy">
            Dokumen ini mengikuti template resmi yang disediakan dan akan dilengkapi tanda tangan digital serta kode verifikasi setelah disetujui Kabid.
        </p>
        @endif

        @if($pengajuanSurat->digitalSignature)
        @php
        $signature = $pengajuanSurat->digitalSignature;
        $verificationUrl = route('verification.show', $signature->verification_code);
        @endphp
        @endif

        <section class="signature-space">
            <div class="signature-box">
                <div>Kepala Bidang</div>
                <div class="signature-line {{ $pengajuanSurat->digitalSignature ? '' : 'unsigned' }}">
                    @if($pengajuanSurat->digitalSignature)
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=112x112&data={{ urlencode($verificationUrl) }}" alt="QR verifikasi {{ $signature->verification_code }}">
                    <code>{{ $signature->verification_code }}</code>
                    <div class="signature-note">Ditandatangani digital</div>
                    @endif
                </div>
                <strong>{{ $pengajuanSurat->digitalSignature?->signer->name ?? '________________________' }}</strong>
                @if($pengajuanSurat->digitalSignature)
                <div style="font-size: 11px; color: #475569;">{{ $signature->signed_at->format('d/m/Y H:i') }} WIB</div>
                @endif
            </div>
        </section>
    </main>
</body>

</html>
