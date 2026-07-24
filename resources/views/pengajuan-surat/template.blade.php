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
            width: 240px;
        }

        .signature-line {
            border-bottom: 1px solid #111827;
            height: 76px;
            margin-bottom: 8px;
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
    </style>
</head>

<body>
    @unless($isPrint)
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

        <p class="body-copy">
            Dokumen ini merupakan template awal. Proses persetujuan, tanda tangan digital,
            dan QR code verifikasi akan dilengkapi pada fase implementasi berikutnya.
        </p>

        <section class="signature-space">
            <div class="signature-box">
                <div>Kepala Bidang</div>
                <div class="signature-line"></div>
                <strong>________________________</strong>
            </div>
        </section>
    </main>
</body>

</html>
