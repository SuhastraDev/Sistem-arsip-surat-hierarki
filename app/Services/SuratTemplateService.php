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
                'template_label' => 'Surat Cuti.docx',
                'template_docx' => 'Surat Cuti.docx',
                'template_note' => 'Template resmi cuti: data pegawai, jenis cuti, alasan, alamat, dan pertimbangan atasan.',
                'fields' => [
                    'nama_pegawai' => ['label' => 'Nama pegawai', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.name', 'placeholder' => 'Terisi otomatis dari akun'],
                    'nip' => ['label' => 'NIP', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.nip', 'placeholder' => 'Terisi otomatis dari akun'],
                    'jabatan_unit' => ['label' => 'Jabatan / unit kerja', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.jabatan', 'placeholder' => 'Terisi otomatis dari akun'],
                    'unit_kerja' => ['label' => 'Unit kerja', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Dinas Kehutanan Provinsi Sumatera Selatan'],
                    'jenis_cuti' => ['label' => 'Jenis cuti', 'type' => 'select', 'required' => true, 'options' => ['Cuti tahunan', 'Cuti sakit', 'Cuti melahirkan', 'Cuti alasan penting']],
                    'tanggal_mulai' => ['label' => 'Tanggal mulai', 'type' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tanggal selesai', 'type' => 'date', 'required' => true],
                    'lama_cuti' => ['label' => 'Lama cuti', 'type' => 'text', 'required' => true, 'readonly' => true, 'auto_calculated' => true, 'placeholder' => 'Terisi otomatis dari tanggal mulai dan selesai'],
                    'alasan' => ['label' => 'Alasan cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Keperluan keluarga di luar kota'],
                    'alamat_selama_cuti' => ['label' => 'Alamat selama cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Jl. Merdeka No. 10, Bandung'],
                    'telepon' => ['label' => 'Telepon', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 0812-3456-7890'],
                    'atasan_langsung' => ['label' => 'Atasan langsung', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'user.atasan_langsung', 'placeholder' => 'Terisi otomatis dari Kasi akun staff'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'file', 'required' => false, 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', 'placeholder' => 'Upload surat dokter atau dokumen pendukung jika ada'],
                ],
            ],
            'surat-tugas' => [
                'title' => 'Surat Tugas',
                'summary' => 'Mengikuti template Surat Perintah Tugas yang disediakan.',
                'template_label' => 'Surat Tugas.doc',
                'template_docx' => 'Surat Tugas.docx',
                'template_note' => 'Template resmi SPT: dasar surat, daftar pegawai yang bepergian, kegiatan, tujuan perjalanan, lama perjalanan, dan penandatangan.',
                'fields' => [
                    'nomor_surat' => ['label' => 'Nomor surat', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'surat_tugas.nomor_surat', 'placeholder' => 'Terisi otomatis oleh sistem'],
                    'dasar_pertama' => ['label' => 'Dasar pertama', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016 tentang Susunan Organisasi...'],
                    'dasar_kedua' => ['label' => 'Dasar kedua', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Surat Kepala Bappeda Nomor ... tentang Peningkatan Kapasitas...'],
                    'pegawai_berangkat' => ['label' => 'Yang bepergian', 'type' => 'textarea', 'required' => true, 'placeholder' => "Contoh:\n1. Muhammad Kangau Rizki Akbar - NIP ... - Penata Muda/IX - Penata Layanan Operasional\n2. Vika Kusumaningrum - NIP ... - Pengatur Muda/V - Pengadministrasi Perkantoran"],
                    'kegiatan' => ['label' => 'Kegiatan yang dihadiri', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Menghadiri Kegiatan Peningkatan Kapasitas dalam Rangka Pembangunan Rendah Karbon Daerah...'],
                    'tujuan_perjalanan' => ['label' => 'Tujuan perjalanan', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Aston Palembang Hotel & Conference Center, Jl. Jend. Basuki Rachmat No.189...'],
                    'tanggal_mulai_perjalanan' => ['label' => 'Tanggal mulai perjalanan', 'type' => 'date', 'required' => true],
                    'tanggal_selesai_perjalanan' => ['label' => 'Tanggal selesai perjalanan', 'type' => 'date', 'required' => true],
                    'lama_perjalanan' => ['label' => 'Lama / tanggal perjalanan', 'type' => 'text', 'required' => true, 'readonly' => true, 'auto_calculated' => true, 'placeholder' => 'Terisi otomatis dari tanggal perjalanan'],
                    'keterangan_biaya' => ['label' => 'Keterangan biaya', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Biaya kegiatan dibebankan pada Badan Perencanaan Pembangunan Daerah.'],
                    'kewajiban_laporan' => ['label' => 'Kewajiban laporan', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Membuat laporan tertulis 1 (satu) minggu setelah pelaksanaan tugas.'],
                    'penandatangan' => ['label' => 'Penandatangan', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'nota.kabid', 'placeholder' => 'Terisi otomatis oleh sistem'],
                ],
            ],
            'nota-dinas' => [
                'title' => 'Nota Dinas',
                'summary' => 'Mengikuti contoh Nota Dinas IKK Februari 2026 yang disediakan.',
                'template_label' => 'Nota Dinas.pdf',
                'template_docx' => 'Nota Dinas.docx',
                'template_note' => 'Template resmi nota dinas: kepada, tembusan, dari, tanggal, nomor, lampiran, perihal, isi nota, dan lampiran capaian.',
                'fields' => [
                    'kepada' => ['label' => 'Kepada Yth.', 'type' => 'text', 'required' => true, 'readonly' => true, 'source' => 'nota.kepada', 'placeholder' => 'Terisi otomatis oleh sistem'],
                    'tembusan' => ['label' => 'Tembusan', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Sekretaris u.b Kasubbag. Perencanaan, Evaluasi dan Pelaporan'],
                    'dari' => ['label' => 'Dari', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Kepala Bidang Perlindungan dan KSDAE'],
                    'tanggal_nota' => ['label' => 'Tanggal nota', 'type' => 'date', 'required' => true],
                    'nomor_nota' => ['label' => 'Nomor nota', 'type' => 'text', 'required' => true, 'readonly' => true, 'auto_calculated' => true, 'placeholder' => 'Terisi otomatis oleh sistem'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'file', 'required' => false, 'accept' => '.pdf,.doc,.docx,.jpg,.jpeg,.png', 'placeholder' => 'Upload lampiran nota dinas jika ada'],
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
                'template_docx' => 'Surat Undangan.docx',
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

    public function previewPdf(PengajuanSurat $pengajuanSurat): ?Response
    {
        $pdf = $this->storedSignedArtifact($pengajuanSurat, 'pdf')
            ?? $this->docxToPdfBinary($this->docxBinary($pengajuanSurat));

        if (! $pdf) {
            if (app()->environment('production') && $this->usesOfficialTemplate($pengajuanSurat)) {
                abort(503, 'Preview PDF template belum bisa dibuat. Pastikan LibreOffice/soffice aktif di server.');
            }

            return null;
        }

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->fileName($pengajuanSurat, 'pdf').'"',
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
        if ($this->usesOfficialTemplate($pengajuanSurat)) {
            $pdf = $this->docxToPdfBinary($this->docxBinary($pengajuanSurat));

            if (! $pdf) {
                if (app()->environment('production')) {
                    abort(503, 'PDF template belum bisa dibuat. Pastikan LibreOffice/soffice aktif di server.');
                }

                return $this->makeSimplePdf($pengajuanSurat);
            }

            return $pdf;
        }

        return $this->makeSimplePdf($pengajuanSurat);
    }

    private function usesOfficialTemplate(PengajuanSurat $pengajuanSurat): bool
    {
        return in_array($pengajuanSurat->jenisSurat->slug, ['surat-cuti', 'surat-tugas', 'nota-dinas', 'surat-undangan'], true);
    }

    public function docxBinary(PengajuanSurat $pengajuanSurat): string
    {
        return match ($pengajuanSurat->jenisSurat->slug) {
            'surat-cuti' => $this->makeSuratCutiDocx($pengajuanSurat),
            'surat-tugas' => $this->makeSuratTugasDocx($pengajuanSurat),
            'nota-dinas' => $this->makeNotaDinasDocx($pengajuanSurat),
            'surat-undangan' => $this->makeSuratUndanganDocx($pengajuanSurat),
            default => $this->makeSimpleDocx($this->plainText($pengajuanSurat)),
        };
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

    private function docxToPdfBinary(string $docx): ?string
    {
        $binary = $this->officeBinary();

        if (! $binary) {
            return null;
        }

        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'surat-pdf-'.Str::random(10);

        if (! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            return null;
        }

        $docxPath = $directory.DIRECTORY_SEPARATOR.'dokumen.docx';
        $pdfPath = $directory.DIRECTORY_SEPARATOR.'dokumen.pdf';
        $profilePath = $directory.DIRECTORY_SEPARATOR.'libreoffice-profile';
        file_put_contents($docxPath, $docx);

        $command = [
            $binary,
            '--headless',
            '--nologo',
            '--nofirststartwizard',
            '--nodefault',
            '--nolockcheck',
            '-env:UserInstallation='.str_replace(DIRECTORY_SEPARATOR, '/', 'file:///'.$profilePath),
            '--convert-to',
            'pdf',
            '--outdir',
            $directory,
            $docxPath,
        ];
        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! is_resource($process)) {
            $this->removeDirectory($directory);

            return null;
        }

        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0 || ! is_file($pdfPath)) {
            $this->removeDirectory($directory);

            return null;
        }

        $pdf = file_get_contents($pdfPath) ?: null;
        $this->removeDirectory($directory);

        return $pdf;
    }

    private function officeBinary(): ?string
    {
        foreach (['soffice', 'libreoffice'] as $binary) {
            if (! $this->commandExists($binary)) {
                continue;
            }

            $process = proc_open([$binary, '--version'], [
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ], $pipes);

            if (! is_resource($process)) {
                continue;
            }

            stream_get_contents($pipes[1]);
            stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            if (proc_close($process) === 0) {
                return $binary;
            }
        }

        return null;
    }

    private function commandExists(string $binary): bool
    {
        $checker = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $arguments = [$checker, $binary];
        $process = proc_open($arguments, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! is_resource($process)) {
            return false;
        }

        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }

    private function removeDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;
            is_dir($path) ? $this->removeDirectory($path) : @unlink($path);
        }

        @rmdir($directory);
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

    private function makeSuratCutiDocx(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeTemplateDocx($pengajuanSurat, 'surat-cuti', function (string $xml, array $data, PengajuanSurat $pengajuanSurat): string {
            $xml = $this->replaceWordText($xml, 'Palembang, 25 Maret 2026', 'Palembang, '.$this->formatIndonesianLongDate($pengajuanSurat->tanggal_pengajuan->toDateString()));
            $xml = $this->replaceWordText($xml, 'I Gusti Ayu Kusuma Wardani, S.Hut', $data['nama_pegawai'] ?? '-');
            $xml = $this->replaceWordText($xml, '199402012022032011', $data['nip'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Polisi Kehutanan', $data['jabatan_unit'] ?? '-');
            $xml = $this->replaceWordText($xml, '4 tahun', '-');
            $xml = $this->replaceWordText($xml, 'Bidang Perlindungan dan KSDAE', $data['unit_kerja'] ?? '-', 1);
            $xml = $this->replaceWordText($xml, 'Keperluan keluarga', $data['alasan'] ?? '-');
            $xml = $this->replaceWordText($xml, '1 HARI', strtoupper($data['lama_cuti'] ?? '-'));
            $xml = $this->replaceWordText($xml, '30 Maret 2026', $this->formatIndonesianLongDate($data['tanggal_mulai'] ?? null));
            $xml = $this->replaceWordText($xml, 'Lampung', $data['alamat_selama_cuti'] ?? '-');
            $xml = $this->replaceWordText($xml, '0813 7391 4100', $data['telepon'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Hormat Saya, I Gusti Ayu Kusuma Wardani, S.Hut NIP. 19940201 202203 2 011', "Hormat Saya,\n\n\n".($data['nama_pegawai'] ?? '-')."\nNIP. ".($data['nip'] ?? '-'));
            $xml = $this->replaceWordText($xml, 'Plh. Kepala Seksi Pengendalian Kerusakan dan Pengamanan Hutan, Ferry Yurisman, S.P NIP. 19730228 199003 1 009', $data['atasan_langsung'] ?? '-');

            return $this->markCutiType($xml, $data['jenis_cuti'] ?? '');
        });
    }

    private function makeSuratTugasDocx(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeTemplateDocx($pengajuanSurat, 'surat-tugas', function (string $xml, array $data, PengajuanSurat $pengajuanSurat): string {
            $xml = $this->replaceWordText($xml, '800.1.11.1/         /ST/Dishut.III/2026', $data['nomor_surat'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Tanggal : Juli 2026', 'Tanggal'."\t\t".': '.$this->formatIndonesianLongDate($pengajuanSurat->tanggal_pengajuan->toDateString()));
            $xml = $this->replaceWordText($xml, 'Peraturan Gubernur Sumatera Selatan Nomor: 48 Tahun 2016 tentang Susunan Organisasi, Uraian Tugas dan Fungsi Dinas Kehutanan Provinsi Sumatera Selatan.', $data['dasar_pertama'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Surat Kepala Badan Perencanaan Pembangunan Daerah Nomor : 000.1.5/1517/Bappeda-IV/2026 Tanggal 21 Juli 2026 tentang Peningkatan Kapasitas dalam Rangka Pembangunan Rendah Karbon Daerah di Provinsi Sumatera Selatan.', $data['dasar_kedua'] ?? '-');
            $xml = $this->fillSuratTugasPegawai($xml, $data['pegawai_berangkat'] ?? '');
            $xml = $this->replaceWordText($xml, 'Menghadiri Kegiatan Peningkatan Kapasitas dalam Rangka Pembangunan Rendah Karbon Daerah di Provinsi Sumatera Selatan', $data['kegiatan'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Aston Pallembang Hotel & Conference Center Jl. Jend. Basuki Rachmat o.189, Talang Aman, Kec. Kemuning, Kota Palembang, Sumatera Selatan 30126', $data['tujuan_perjalanan'] ?? '-');
            $xml = $this->replaceWordText($xml, '5 (lima) hari / 27-31 Juli 2026', $data['lama_perjalanan'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Biaya yang timbul dari kegiatan tersebut dibebankan pada Badan Perencanaan Pembangunan Daerah. Membuat Laporan tertulis hasil pelaksanaan tugas tersebut 1 (satu) minggu setelah pelaksanaan tugas kepada Kepala Dinas Kehutanan Provinsi Sumatera Selatan.', trim(($data['keterangan_biaya'] ?? '')."\n".($data['kewajiban_laporan'] ?? '')) ?: '-');

            if ($pengajuanSurat->digitalSignature) {
                $signatureText = 'Ditandatangani digital: '.$pengajuanSurat->digitalSignature->verification_code;
                $xml = $this->replaceWordText($xml, 'SUSILO HARTONO, S.Hut., M.Si', $signatureText."\n".$pengajuanSurat->digitalSignature->signer->name);
            } elseif (! empty($data['penandatangan'])) {
                $xml = $this->replaceWordText($xml, 'SUSILO HARTONO, S.Hut., M.Si', $data['penandatangan']);
            }

            return $xml;
        });
    }

    private function makeNotaDinasDocx(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeTemplateDocx($pengajuanSurat, 'nota-dinas', function (string $xml, array $data): string {
            $xml = $this->replaceWordText($xml, 'Kepala Dinas Kehutanan Provinsi Sumatera Selatan', $data['kepada'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Sekretaris u.b Kasubbag. Perencanaan, Evaluasi dan Pelaporan', $data['tembusan'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Kepala Bidang Perlindungan dan KSDAE', $data['dari'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Maret 2026', $this->formatIndonesianLongDate($data['tanggal_nota'] ?? null), 1);
            $xml = $this->replaceWordText($xml, '500.0.0.0/            /ND.DISHUT/I/2026', $data['nomor_nota'] ?? '-');
            $xml = $this->replaceWordText($xml, '1 (satu) berkas', isset($data['lampiran']) ? '1 (satu) berkas' : '-');
            $xml = $this->replaceWordText($xml, 'Penyampaian Capaian Indikator Kinerja Kunci (IKK) Bulan Februari 2026', $data['perihal_nota'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Menindaklanjuti Nota Dinas Kepala Dinas Kehutanan Nomor 000.7.2.8/126/ND. DISHUT/I/2026 perihal Capaian Indikator Kinerja Kunci (IKK) Bulan Februari Tahun 2026, bersama ini kami sampaikan laporan capaian IKK Bidang Perlindungan dan KSDAE sampai dengan Bulan Februari Tahun 2026. Berdasarkan hasil pengumpulan data dari masing-masing bidang teknis, capaian IKK Bulan Februari 2026 menunjukkan progres yang berjalan sesuai dengan target tahunan yang telah ditetapkan dalam dokumen perencanaan kinerja. Realisasi kinerja dihitung berdasarkan perbandingan antara target tahunan dengan capaian kumulatif sampai dengan bulan berjalan.', $data['isi_nota'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Dr. SYAFRUL YUNARDY, S.Hut., M.E', $data['penandatangan'] ?? '-');

            return $xml;
        });
    }

    private function makeSuratUndanganDocx(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeTemplateDocx($pengajuanSurat, 'surat-undangan', function (string $xml, array $data, PengajuanSurat $pengajuanSurat): string {
            $xml = $this->replaceWordText($xml, 'Palembang, 28 Juli 2026', 'Palembang, '.$this->formatIndonesianLongDate($pengajuanSurat->tanggal_pengajuan->toDateString()));
            $xml = $this->replaceWordText($xml, '500.4.6.4/3508/Dishut.III/2025', $data['nomor_surat'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Biasa', $data['sifat'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Lampiran : -', 'Lampiran'."\t".':'."\t".($data['lampiran'] ?? '-'));
            $xml = $this->replaceWordText($xml, 'Undangan Rapat', $data['hal'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Kepala UPTD KPH Wilayah VIII Semendo Direktur PT. Genus Rona Hijau Direktur Yayasan Relung Indonesia Bakhtiar S. Aji di - Tempat', ($data['tujuan_undangan'] ?? '-')."\ndi - Tempat");
            $xml = $this->replaceWordText($xml, 'Dalam rangka percepatan pelaksanaan implementasi RBP REDD+ GCF Output II KP 2 Provinsi Sumatera Selatan melalui kegiatan Inventarisasi Potensi Kawasan Bernilai Ekosistem Penting (KBEP) Kewenangan Daerah Provinsi Sumatera Selatan Tahun Anggaran 2026, diperlukan koordinasi awal untuk memperoleh kesamaan persepsi mengenai ruang lingkup pekerjaan, metodologi pelaksanaan, serta mekanisme koordinasi antar pihak yang terlibat. Berkenaan dengan hal tersebut, kami mengundang Bapak/Ibu untuk mengikuti rapat yang akan diselenggarakan secara daring pada:', $data['latar_belakang'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Jumat/31 Agustus 2026', $data['hari_tanggal'] ?? '-');
            $xml = $this->replaceWordText($xml, '10.00 WIB s.d Selesai', $data['waktu'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Zoom meeting pada tautan https://bit.ly/Ranker-KBEP', $data['tempat'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Meeting ID: 897 5674 5132', 'Meeting ID: '.($data['meeting_id'] ?? '-'));
            $xml = $this->replaceWordText($xml, 'Passcode: 629115', 'Passcode: '.($data['passcode'] ?? '-'));
            $xml = $this->replaceWordText($xml, 'Penyampaian Rencana Kerja Tenaga Ahli Inventarisasi Potensi Kawasan', $data['agenda'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Bernilai Ekosistem Penting (KBEP) Kewenangan Daerah Provinsi Sumatera Selatan Tahun Anggaran 2026', '');
            $xml = $this->replaceWordText($xml, 'Sdri. I Gusti Ayu Kusuma Wardani (0813-7391-4100).', $data['kontak_konfirmasi'] ?? '-');
            $xml = $this->replaceWordText($xml, 'Drs. H. KOIMUDIN, S.H., M.M', $data['penandatangan'] ?? '-');

            return $xml;
        });
    }

    private function makeTemplateDocx(PengajuanSurat $pengajuanSurat, string $slug, callable $mapper): string
    {
        $templateDocx = $this->definition($slug)['template_docx'] ?? null;
        $templatePath = $templateDocx ? base_path('template/'.$templateDocx) : null;

        if (! $templatePath || ! is_file($templatePath)) {
            return $this->makeSimpleDocx($this->plainText($pengajuanSurat));
        }

        $pengajuanSurat->loadMissing(['digitalSignature.signer']);
        $data = $pengajuanSurat->metadata['form_data'] ?? [];
        $temp = tempnam(sys_get_temp_dir(), 'template-docx');
        copy($templatePath, $temp);
        $zip = new ZipArchive;
        $zip->open($temp);
        $xml = $zip->getFromName('word/document.xml');

        if (! is_string($xml)) {
            $zip->close();

            return $this->makeSimpleDocx($this->plainText($pengajuanSurat));
        }

        $xml = $mapper($xml, $data, $pengajuanSurat);
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        $content = file_get_contents($temp);
        @unlink($temp);

        return $content ?: '';
    }

    private function fillSuratTugasPegawai(string $xml, string $rawPegawai): string
    {
        $pegawai = $this->parsePegawaiBerangkat($rawPegawai);
        $defaults = [
            ['Muhammad Kangau Rizki Akbar, S.Hut', '19990906202521 1 021', 'Penata Muda/IX', 'Penata Layanan Operasional'],
            ['Vika Kusumaningrum', '19910207202521 2 044', 'Pengatur Muda/V', 'Pengadministrasi Perkantoran'],
        ];

        foreach ($defaults as $index => [$nama, $nip, $pangkat, $jabatan]) {
            $item = $pegawai[$index] ?? ['nama' => '-', 'nip' => '-', 'pangkat' => '-', 'jabatan' => '-'];
            $xml = $this->replaceWordText($xml, $nama, $item['nama']);
            $xml = $this->replaceWordText($xml, $nip, $item['nip']);
            $xml = $this->replaceWordText($xml, $pangkat, $item['pangkat']);
            $xml = $this->replaceWordText($xml, $jabatan, $item['jabatan']);
        }

        return $xml;
    }

    private function parsePegawaiBerangkat(string $raw): array
    {
        return collect(preg_split('/\R+/', trim($raw)) ?: [])
            ->filter()
            ->map(function (string $line): array {
                $line = trim(preg_replace('/^\d+\.\s*/', '', $line) ?? $line);
                $parts = array_values(array_filter(array_map('trim', preg_split('/\s+-\s+/', $line) ?: [])));

                return [
                    'nama' => $parts[0] ?? $line,
                    'nip' => preg_replace('/^NIP\.?\s*/i', '', $parts[1] ?? '-'),
                    'pangkat' => $parts[2] ?? '-',
                    'jabatan' => $parts[3] ?? '-',
                ];
            })
            ->values()
            ->all();
    }

    private function replaceWordText(string $xml, string $search, string $replacement, ?int $occurrence = null): string
    {
        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return $xml;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $searchText = $this->normalizeWordText($search);
        $found = 0;

        foreach ($xpath->query('//w:p|//w:tc') as $container) {
            $textNodes = $xpath->query('.//w:t', $container);

            if (! $textNodes || $textNodes->length === 0) {
                continue;
            }

            $current = '';

            foreach ($textNodes as $node) {
                $current .= $node->nodeValue;
            }

            if (! str_contains($this->normalizeWordText($current), $searchText)) {
                continue;
            }

            $found++;

            if ($occurrence !== null && $found !== $occurrence) {
                continue;
            }

            $updated = str_replace($search, $replacement, $current, $count);

            if ($count === 0) {
                $pattern = '/'.implode('\s+', array_map(
                    fn (string $token): string => preg_quote($token, '/'),
                    preg_split('/\s+/', trim($search)) ?: []
                )).'/u';
                $updated = preg_replace($pattern, $replacement, $current, 1, $count) ?? $current;
            }

            if ($count === 0 && $this->normalizeWordText($current) === $searchText) {
                $updated = $replacement;
                $count = 1;
            }

            if ($count === 0) {
                continue;
            }

            $textNodes->item(0)->nodeValue = $updated;

            for ($i = 1; $i < $textNodes->length; $i++) {
                $textNodes->item($i)->nodeValue = '';
            }

            return $dom->saveXML() ?: $xml;
        }

        return $xml;
    }

    private function markCutiType(string $xml, string $jenisCuti): string
    {
        $markers = [
            'Cuti tahunan' => '1. Cuti Tahunan',
            'Cuti sakit' => '3. Cuti Sakit',
            'Cuti melahirkan' => '4. Cuti Melahirkan',
            'Cuti alasan penting' => '5. Cuti Karena Alasan Penting',
        ];

        if (! isset($markers[$jenisCuti])) {
            return $xml;
        }

        return $this->replaceWordText($xml, $markers[$jenisCuti], $markers[$jenisCuti].' ✓');
    }

    private function normalizeWordText(string $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    private function formatIndonesianLongDate(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $timestamp = strtotime($date);

        return date('j', $timestamp).' '.$months[(int) date('n', $timestamp)].' '.date('Y', $timestamp);
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
