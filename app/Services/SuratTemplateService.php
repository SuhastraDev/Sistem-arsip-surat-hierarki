<?php

namespace App\Services;

use App\Models\PengajuanSurat;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class SuratTemplateService
{
    public function definitions(): array
    {
        return [
            'surat-cuti' => [
                'title' => 'Surat Cuti',
                'summary' => 'Mengikuti formulir permintaan dan pemberian cuti BKN yang disediakan.',
                'template_label' => 'SURAT CUTII_GUSTI_2026.docx',
                'template_note' => 'Template resmi cuti: data pegawai, jenis cuti, alasan, alamat, dan pertimbangan atasan.',
                'fields' => [
                    'nama_pegawai' => ['label' => 'Nama pegawai', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.name', 'placeholder' => 'Terisi otomatis dari akun'],
                    'nip' => ['label' => 'NIP', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.nip', 'placeholder' => 'Terisi otomatis dari akun'],
                    'jabatan_unit' => ['label' => 'Jabatan / unit kerja', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.jabatan', 'placeholder' => 'Terisi otomatis dari akun'],
                    'masa_kerja' => ['label' => 'Masa kerja', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 12 tahun 4 bulan'],
                    'unit_kerja' => ['label' => 'Unit kerja', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Dinas Kehutanan Provinsi Sumatera Selatan'],
                    'jenis_cuti' => ['label' => 'Jenis cuti', 'type' => 'select', 'required' => true, 'options' => ['Cuti tahunan', 'Cuti sakit', 'Cuti melahirkan', 'Cuti alasan penting']],
                    'tanggal_mulai' => ['label' => 'Tanggal mulai', 'type' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tanggal selesai', 'type' => 'date', 'required' => true],
                    'lama_cuti' => ['label' => 'Lama cuti', 'type' => 'text', 'required' => true, 'readonly' => true, 'auto_calculated' => true, 'placeholder' => 'Terisi otomatis dari tanggal mulai dan selesai'],
                    'alasan' => ['label' => 'Alasan cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Keperluan keluarga di luar kota'],
                    'alamat_selama_cuti' => ['label' => 'Alamat selama cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Jl. Merdeka No. 10, Bandung'],
                    'telepon' => ['label' => 'Telepon', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 0812-3456-7890'],
                    'atasan_langsung' => ['label' => 'Atasan langsung', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Ibu Siti - Kasi Rehabilitasi Hutan'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'file', 'required' => false, 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', 'placeholder' => 'Upload surat dokter atau dokumen pendukung jika ada'],
                ],
            ],
            'surat-tugas' => [
                'title' => 'Surat Tugas',
                'summary' => 'Mengikuti template Surat Perintah Tugas yang disediakan.',
                'template_label' => 'SPT Bappeda 27 sd 31 Juli 2026.doc',
                'template_note' => 'Template resmi SPT: dasar surat, daftar pegawai yang bepergian, kegiatan, tujuan perjalanan, lama perjalanan, dan penandatangan.',
                'fields' => [
                    'nomor_surat' => ['label' => 'Nomor surat', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: 800.1.11.1/        /ST/Dishut.III/2026'],
                    'dasar_pertama' => ['label' => 'Dasar pertama', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016 tentang Susunan Organisasi...'],
                    'dasar_kedua' => ['label' => 'Dasar kedua', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Surat Kepala Bappeda Nomor ... tentang Peningkatan Kapasitas...'],
                    'pegawai_berangkat' => ['label' => 'Yang bepergian', 'type' => 'textarea', 'required' => true, 'placeholder' => "Contoh:\n1. Muhammad Kangau Rizki Akbar - NIP ... - Penata Muda/IX - Penata Layanan Operasional\n2. Vika Kusumaningrum - NIP ... - Pengatur Muda/V - Pengadministrasi Perkantoran"],
                    'kegiatan' => ['label' => 'Kegiatan yang dihadiri', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Menghadiri Kegiatan Peningkatan Kapasitas dalam Rangka Pembangunan Rendah Karbon Daerah...'],
                    'tujuan_perjalanan' => ['label' => 'Tujuan perjalanan', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Aston Palembang Hotel & Conference Center, Jl. Jend. Basuki Rachmat No.189...'],
                    'lama_perjalanan' => ['label' => 'Lama / tanggal perjalanan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 5 (lima) hari / 27-31 Juli 2026'],
                    'keterangan_biaya' => ['label' => 'Keterangan biaya', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Biaya kegiatan dibebankan pada Badan Perencanaan Pembangunan Daerah.'],
                    'kewajiban_laporan' => ['label' => 'Kewajiban laporan', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Membuat laporan tertulis 1 (satu) minggu setelah pelaksanaan tugas.'],
                    'penandatangan' => ['label' => 'Penandatangan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: SUSILO HARTONO, S.Hut., M.Si - Sekretaris'],
                ],
            ],
            'nota-dinas' => [
                'title' => 'Nota Dinas',
                'summary' => 'Mengikuti contoh Nota Dinas IKK Februari 2026 yang disediakan.',
                'template_label' => 'Nota Dinas_IKK Februari 2026.pdf',
                'template_note' => 'Template resmi nota dinas: kepada, tembusan, dari, tanggal, nomor, lampiran, perihal, isi nota, dan lampiran capaian.',
                'fields' => [
                    'kepada' => ['label' => 'Kepada Yth.', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'nota.kepada', 'placeholder' => 'Terisi otomatis oleh sistem'],
                    'tembusan' => ['label' => 'Tembusan', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Sekretaris u.b Kasubbag. Perencanaan, Evaluasi dan Pelaporan'],
                    'dari' => ['label' => 'Dari', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Kepala Bidang Perlindungan dan KSDAE'],
                    'tanggal_nota' => ['label' => 'Tanggal nota', 'type' => 'date', 'required' => true],
                    'nomor_nota' => ['label' => 'Nomor nota', 'type' => 'text', 'required' => true, 'readonly' => true, 'auto_calculated' => true, 'placeholder' => 'Terisi otomatis oleh sistem'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: 1 (satu) berkas'],
                    'perihal_nota' => ['label' => 'Perihal', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Penyampaian Capaian Indikator Kinerja Kunci (IKK) Bulan Februari 2026'],
                    'isi_nota' => ['label' => 'Isi nota', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Menindaklanjuti Nota Dinas Kepala Dinas Kehutanan Nomor ... bersama ini kami sampaikan...'],
                    'rincian_lampiran' => ['label' => 'Rincian lampiran', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Tabel capaian indikator, target, capaian bulan ini, capaian s.d bulan ini, dan keterangan.'],
                    'penandatangan' => ['label' => 'Penandatangan', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'nota.kabid', 'placeholder' => 'Terisi otomatis oleh sistem'],
                ],
            ],
            'surat-undangan' => [
                'title' => 'Surat Undangan Rapat',
                'summary' => 'Mengikuti template undangan rapat Genus Rona yang disediakan.',
                'template_label' => '20260729080454_Surat undangan Rapat Genus Rona_31 Agustus 2026.pdf',
                'template_note' => 'Template resmi undangan: nomor, sifat, lampiran, hal, tujuan undangan, jadwal rapat, tautan, agenda, kontak, dan penandatangan.',
                'fields' => [
                    'nomor_surat' => ['label' => 'Nomor surat', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: 500.4.6.4/3508/Dishut.III/2026'],
                    'sifat' => ['label' => 'Sifat', 'type' => 'select', 'required' => true, 'options' => ['Biasa', 'Penting', 'Segera', 'Rahasia']],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: - / 1 (satu) berkas'],
                    'hal' => ['label' => 'Hal', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Undangan Rapat'],
                    'tujuan_undangan' => ['label' => 'Tujuan undangan', 'type' => 'textarea', 'required' => true, 'placeholder' => "Contoh:\n1. Kepala UPTD KPH Wilayah VIII Semendo\n2. Direktur PT. Genus Rona Hijau\n3. Direktur Yayasan Relung Indonesia"],
                    'latar_belakang' => ['label' => 'Latar belakang / pembuka', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Dalam rangka percepatan pelaksanaan implementasi RBP REDD+ GCF Output II...'],
                    'hari_tanggal' => ['label' => 'Hari / tanggal', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Jumat / 31 Agustus 2026'],
                    'waktu' => ['label' => 'Waktu', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 10.00 WIB s.d Selesai'],
                    'tempat' => ['label' => 'Tempat / tautan rapat', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Zoom meeting pada tautan https://bit.ly/Ranker-KBEP'],
                    'meeting_id' => ['label' => 'Meeting ID', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: 897 5674 5132'],
                    'passcode' => ['label' => 'Passcode', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: 629115'],
                    'agenda' => ['label' => 'Agenda', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Penyampaian Rencana Kerja Tenaga Ahli Inventarisasi Potensi Kawasan Bernilai Ekosistem Penting...'],
                    'kontak_konfirmasi' => ['label' => 'Kontak konfirmasi', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Sdri. I Gusti Ayu Kusuma Wardani (0813-7391-4100)'],
                    'penandatangan' => ['label' => 'Penandatangan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Drs. H. KOIMUDIN, S.H., M.M - Kepala Dinas'],
                ],
            ],
        ];
    }

    public function validationRules(string $slug): array
    {
        $rules = [];

        foreach ($this->fields($slug) as $key => $field) {
            $isServerFilled = isset($field['source']) || ($field['auto_calculated'] ?? false);
            $presenceRule = (($field['required'] ?? false) && ! $isServerFilled) ? 'required' : 'nullable';

            if (($field['type'] ?? null) === 'file') {
                $rules['fields.'.$key] = [
                    $presenceRule,
                    'file',
                    'mimes:pdf,doc,docx,jpg,jpeg,png',
                    'max:5120',
                ];

                continue;
            }

            $rules['fields.'.$key] = [$presenceRule, 'string', 'max:2000'];
        }

        return $rules;
    }

    public function fields(string $slug): array
    {
        return $this->definitions()[$slug]['fields'] ?? [];
    }

    public function definition(string $slug): array
    {
        return $this->definitions()[$slug] ?? [
            'title' => 'Surat',
            'summary' => 'Template surat sistem.',
            'fields' => [],
        ];
    }

    public function cleanFields(string $slug, array $input): array
    {
        $allowed = array_keys($this->fields($slug));

        return collect(Arr::only($input, $allowed))
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->all();
    }

    public function title(string $slug): string
    {
        return $this->definitions()[$slug]['title'] ?? 'Surat';
    }

    public function templateRows(PengajuanSurat $pengajuanSurat): array
    {
        $slug = $pengajuanSurat->jenisSurat->slug;
        $data = $pengajuanSurat->metadata['form_data'] ?? [];

        return collect($this->fields($slug))
            ->map(fn ($field, $key) => [
                'key' => $key,
                'label' => $field['label'],
                'value' => $this->displayValue($data[$key] ?? null),
            ])
            ->values()
            ->all();
    }

    private function displayValue(mixed $value): string
    {
        if (is_array($value)) {
            return $value['original_name'] ?? $value['name'] ?? '-';
        }

        return filled($value) ? (string) $value : '-';
    }

    public function downloadPdf(PengajuanSurat $pengajuanSurat): Response
    {
        $pdf = $this->storedSignedArtifact($pengajuanSurat, 'pdf') ?? $this->pdfBinary($pengajuanSurat);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$this->fileName($pengajuanSurat, 'pdf').'"',
        ]);
    }

    public function downloadDocx(PengajuanSurat $pengajuanSurat): Response
    {
        $docx = $this->storedSignedArtifact($pengajuanSurat, 'docx') ?? $this->docxBinary($pengajuanSurat);

        return response($docx, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'attachment; filename="'.$this->fileName($pengajuanSurat, 'docx').'"',
        ]);
    }

    public function pdfBinary(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeSimplePdf($pengajuanSurat);
    }

    public function docxBinary(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeSimpleDocx($this->plainText($pengajuanSurat));
    }

    public function canonicalPlainText(PengajuanSurat $pengajuanSurat): array
    {
        $definition = $this->definition($pengajuanSurat->jenisSurat->slug);

        $lines = [
            strtoupper($pengajuanSurat->jenisSurat->nama),
            'Nomor Pengajuan: '.$pengajuanSurat->nomor_pengajuan,
            'Template sumber: '.($definition['template_label'] ?? 'Template sistem'),
            'Tanggal: '.$pengajuanSurat->tanggal_pengajuan->format('d/m/Y'),
            'Pemohon: '.$pengajuanSurat->pemohon->name,
            'Perihal: '.$pengajuanSurat->perihal,
            '',
            'DATA PERSYARATAN',
        ];

        foreach ($this->templateRows($pengajuanSurat) as $row) {
            $lines[] = $row['label'].': '.$row['value'];
        }

        $lines[] = '';
        $lines[] = 'Dokumen final dapat diverifikasi melalui kode verifikasi setelah ditandatangani.';

        return $lines;
    }

    public function plainText(PengajuanSurat $pengajuanSurat): array
    {
        $pengajuanSurat->loadMissing(['digitalSignature.signer']);
        $lines = $this->canonicalPlainText($pengajuanSurat);

        if (! $pengajuanSurat->digitalSignature) {
            return $lines;
        }

        $signature = $pengajuanSurat->digitalSignature;

        return [
            ...$lines,
            '',
            'PENGESAHAN DIGITAL',
            'Status: Dokumen final telah ditandatangani dan dikirim kembali ke Staff pemohon.',
            'Penandatangan: '.$signature->signer->name.' ('.$signature->signer->jabatan.')',
            'Waktu tanda tangan: '.$signature->signed_at->format('d/m/Y H:i').' WIB',
            'Algoritma: '.$signature->algorithm,
            'Kode verifikasi: '.$signature->verification_code,
            'Halaman verifikasi: '.route('verification.show', $signature->verification_code),
        ];
    }

    private function storedSignedArtifact(PengajuanSurat $pengajuanSurat, string $format): ?string
    {
        $pengajuanSurat->loadMissing('digitalSignature');
        $path = $pengajuanSurat->digitalSignature?->metadata['file_paths'][$format] ?? null;

        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            return null;
        }

        return Storage::disk('local')->get($path);
    }

    private function fileName(PengajuanSurat $pengajuanSurat, string $extension): string
    {
        return Str::slug($pengajuanSurat->nomor_pengajuan.'-'.$pengajuanSurat->jenisSurat->nama).'.'.$extension;
    }

    private function makeSimplePdf(PengajuanSurat $pengajuanSurat): string
    {
        $pengajuanSurat->loadMissing(['digitalSignature.signer']);
        $lines = $this->plainText($pengajuanSurat);
        $stream = "BT\n/F1 12 Tf\n50 790 Td\n";
        $lastY = 790;

        foreach ($lines as $index => $line) {
            $safeLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= ($index === 0 ? '/F1 16 Tf ' : '/F1 11 Tf ').'('.$safeLine.") Tj\n0 -20 Td\n";
            $lastY -= 20;
        }

        $stream .= "ET\n";

        if ($pengajuanSurat->digitalSignature) {
            $stream .= $this->pdfSignatureBlock($pengajuanSurat, max(130, $lastY - 36));
        }

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length ".strlen($stream)." >>\nstream\n".$stream."endstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= str_pad((string) $offsets[$i], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n".$xref."\n%%EOF";
    }

    private function pdfSignatureBlock(PengajuanSurat $pengajuanSurat, int $topY): string
    {
        $signature = $pengajuanSurat->digitalSignature;
        $signerName = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $signature->signer->name);
        $verificationCode = $signature->verification_code;
        $barcode = $this->code128Bars($verificationCode);
        $x = 348;
        $y = $topY - 76;
        $height = 42;
        $module = 0.72;
        $stream = "q\n";
        $stream .= "0.94 0.99 0.96 rg\n344 ".($topY - 102)." 190 118 re f\n";
        $stream .= "0.07 0.45 0.40 RG\n344 ".($topY - 102)." 190 118 re S\n";
        $stream .= "BT\n/F1 10 Tf\n384 ".($topY - 12)." Td\n(Kepala Bidang) Tj\nET\n";
        $stream .= "0 0 0 rg\n";

        foreach ($barcode as [$offset, $width]) {
            $stream .= ($x + ($offset * $module)).' '.$y.' '.($width * $module).' '.$height." re f\n";
        }

        $safeCode = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $verificationCode);
        $stream .= "BT\n/F1 8 Tf\n".($x + 22).' '.($y - 12)." Td\n($safeCode) Tj\nET\n";
        $stream .= "BT\n/F1 8 Tf\n".($x + 6).' '.($y - 26)." Td\n(Scan barcode / verifikasi kode) Tj\nET\n";
        $stream .= "BT\n/F1 10 Tf\n".($x + 8).' '.($topY - 96)." Td\n($signerName) Tj\nET\n";
        $stream .= "Q\n";

        return $stream;
    }

    private function code128Bars(string $value): array
    {
        $patterns = [
            '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
            '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
            '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
            '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
            '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
            '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
            '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
            '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
            '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
            '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
            '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
        ];

        $codes = [104];

        foreach (str_split($value) as $character) {
            $ordinal = ord($character);
            $codes[] = ($ordinal >= 32 && $ordinal <= 126) ? $ordinal - 32 : 0;
        }

        $checksum = $codes[0];

        foreach (array_slice($codes, 1) as $position => $code) {
            $checksum += $code * ($position + 1);
        }

        $codes[] = $checksum % 103;
        $codes[] = 106;
        $bars = [];
        $offset = 0;

        foreach ($codes as $code) {
            $pattern = $patterns[$code];

            foreach (str_split($pattern) as $index => $width) {
                $width = (int) $width;

                if ($index % 2 === 0) {
                    $bars[] = [$offset, $width];
                }

                $offset += $width;
            }
        }

        return $bars;
    }

    private function makeSimpleDocx(array $lines): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new ZipArchive;
        $zip->open($temp, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/></Relationships>');

        $paragraphs = collect($lines)
            ->map(fn ($line) => '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($line, ENT_XML1).'</w:t></w:r></w:p>')
            ->implode('');

        $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$paragraphs.'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr></w:body></w:document>');
        $zip->close();

        $content = file_get_contents($temp);
        @unlink($temp);

        return $content ?: '';
    }
}
