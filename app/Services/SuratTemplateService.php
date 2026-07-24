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
                'summary' => 'Lengkapi data cuti pegawai, periode, alasan, dan alamat selama cuti.',
                'fields' => [
                    'nama_pegawai' => ['label' => 'Nama pegawai', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Mas Asep'],
                    'jabatan_unit' => ['label' => 'Jabatan / unit kerja', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Staf Administrasi - Seksi Rehabilitasi Hutan'],
                    'jenis_cuti' => ['label' => 'Jenis cuti', 'type' => 'select', 'required' => true, 'options' => ['Cuti tahunan', 'Cuti sakit', 'Cuti melahirkan', 'Cuti alasan penting']],
                    'tanggal_mulai' => ['label' => 'Tanggal mulai', 'type' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tanggal selesai', 'type' => 'date', 'required' => true],
                    'lama_cuti' => ['label' => 'Lama cuti', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: 5 hari kerja'],
                    'alasan' => ['label' => 'Alasan cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Keperluan keluarga di luar kota'],
                    'alamat_selama_cuti' => ['label' => 'Alamat selama cuti', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Jl. Merdeka No. 10, Bandung'],
                    'atasan_langsung' => ['label' => 'Atasan langsung', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Ibu Siti - Kasi Rehabilitasi Hutan'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Surat dokter / dokumen pendukung, jika ada'],
                ],
            ],
            'surat-tugas' => [
                'title' => 'Surat Tugas',
                'summary' => 'Lengkapi data pegawai, lokasi, periode, dasar, dan uraian tugas.',
                'fields' => [
                    'pegawai_ditugaskan' => ['label' => 'Pegawai ditugaskan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Mas Asep, Mba Dewi'],
                    'jabatan_unit' => ['label' => 'Jabatan / unit kerja', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Staf Lapangan - Bidang Konservasi'],
                    'tujuan_penugasan' => ['label' => 'Tujuan penugasan', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Monitoring rehabilitasi hutan'],
                    'lokasi_tugas' => ['label' => 'Lokasi tugas', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Kawasan Hutan Lindung Gunung X'],
                    'tanggal_mulai' => ['label' => 'Tanggal mulai', 'type' => 'date', 'required' => true],
                    'tanggal_selesai' => ['label' => 'Tanggal selesai', 'type' => 'date', 'required' => true],
                    'dasar_keperluan' => ['label' => 'Dasar / keperluan', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Berdasarkan agenda monitoring triwulan dan arahan Kepala Bidang'],
                    'uraian_tugas' => ['label' => 'Uraian tugas', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Melakukan observasi lapangan, mencatat kondisi tanaman, dan menyusun laporan hasil monitoring'],
                    'pemberi_tugas' => ['label' => 'Pemberi tugas', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Bapak Budi - Kepala Bidang Konservasi'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Jadwal kegiatan / daftar lokasi, jika ada'],
                ],
            ],
            'nota-dinas' => [
                'title' => 'Nota Dinas',
                'summary' => 'Lengkapi tujuan nota, unit pengaju, isi ringkas, prioritas, dan catatan.',
                'fields' => [
                    'perihal_nota' => ['label' => 'Perihal', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Koordinasi pelaksanaan kegiatan rehabilitasi'],
                    'tujuan_penerima' => ['label' => 'Tujuan / penerima', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Kepala Seksi Rehabilitasi Hutan'],
                    'tanggal_pengajuan_nota' => ['label' => 'Tanggal pengajuan', 'type' => 'date', 'required' => true],
                    'unit_pengaju' => ['label' => 'Unit pengaju', 'type' => 'text', 'required' => true, 'placeholder' => 'Contoh: Seksi Rehabilitasi Hutan'],
                    'isi_ringkas' => ['label' => 'Isi ringkas', 'type' => 'textarea', 'required' => true, 'placeholder' => 'Contoh: Mohon koordinasi terkait jadwal, personel, dan kebutuhan lapangan untuk kegiatan minggu depan'],
                    'lampiran' => ['label' => 'Lampiran', 'type' => 'text', 'required' => false, 'placeholder' => 'Contoh: Draft jadwal / daftar kebutuhan, jika ada'],
                    'prioritas' => ['label' => 'Prioritas', 'type' => 'select', 'required' => true, 'options' => ['Normal', 'Penting', 'Segera']],
                    'catatan_tambahan' => ['label' => 'Catatan tambahan', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Contoh: Perlu ditindaklanjuti sebelum hari Jumat'],
                ],
            ],
        ];
    }

    public function validationRules(string $slug): array
    {
        $rules = [];

        foreach ($this->fields($slug) as $key => $field) {
            $rules['fields.'.$key] = [($field['required'] ?? false) ? 'required' : 'nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    public function fields(string $slug): array
    {
        return $this->definitions()[$slug]['fields'] ?? [];
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
                'label' => $field['label'],
                'value' => $data[$key] ?? '-',
            ])
            ->values()
            ->all();
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
        return $this->makeSimplePdf($this->plainText($pengajuanSurat));
    }

    public function docxBinary(PengajuanSurat $pengajuanSurat): string
    {
        return $this->makeSimpleDocx($this->plainText($pengajuanSurat));
    }

    public function plainText(PengajuanSurat $pengajuanSurat): array
    {
        $lines = [
            strtoupper($pengajuanSurat->jenisSurat->nama),
            'Nomor Pengajuan: '.$pengajuanSurat->nomor_pengajuan,
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

    private function makeSimplePdf(array $lines): string
    {
        $stream = "BT\n/F1 12 Tf\n50 790 Td\n";

        foreach ($lines as $index => $line) {
            $safeLine = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $stream .= ($index === 0 ? '/F1 16 Tf ' : '/F1 11 Tf ').'('.$safeLine.") Tj\n0 -20 Td\n";
        }

        $stream .= "ET\n";
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
