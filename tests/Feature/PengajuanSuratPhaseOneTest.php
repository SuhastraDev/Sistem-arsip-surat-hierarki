<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengajuanSuratPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    private function documentXmlFromDocxResponse($response): string
    {
        $temp = tempnam(sys_get_temp_dir(), 'docx-test');
        file_put_contents($temp, $response->getContent());

        $zip = new \ZipArchive;
        $zip->open($temp);
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();
        @unlink($temp);

        return $documentXml ?: '';
    }

    public function test_admin_can_open_master_jenis_surat_with_seeded_types(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('jenis-surat.index'))
            ->assertOk()
            ->assertSee('Surat Cuti')
            ->assertSee('Surat Tugas')
            ->assertSee('Nota Dinas')
            ->assertSee('Surat Undangan Rapat');
    }

    public function test_staff_can_create_pengajuan_surat_to_kasi(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $kasi = User::findOrFail($staff->parent_id);
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-24',
                'perihal' => 'Permohonan surat tugas monitoring lapangan',
                'fields' => [
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan hutan',
                    'tujuan_perjalanan' => 'Kawasan Hutan Lindung',
                    'tanggal_mulai_perjalanan' => '2026-07-25',
                    'tanggal_selesai_perjalanan' => '2026-07-27',
                    'keterangan_biaya' => 'Biaya dibebankan pada kegiatan terkait.',
                    'kewajiban_laporan' => 'Membuat laporan tertulis setelah pelaksanaan tugas.',
                    'penandatangan' => 'Penandatangan palsu',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.index'));

        $this->assertDatabaseHas('pengajuan_surats', [
            'jenis_surat_id' => $jenisSurat->id,
            'pemohon_id' => $staff->id,
            'status' => 'diajukan',
            'posisi_saat_ini' => $kasi->id,
        ]);

        $pengajuan = PengajuanSurat::firstOrFail();

        $this->assertStringStartsWith('PGJ-'.now()->format('Ymd').'-', $pengajuan->nomor_pengajuan);
        $this->assertSame('Monitoring kawasan hutan', $pengajuan->metadata['form_data']['kegiatan']);
        $this->assertStringStartsWith('800.1.11.1/001/ST/Dishut.III/', $pengajuan->metadata['form_data']['nomor_surat']);
        $this->assertSame('3 hari / 25/07/2026 s.d. 27/07/2026', $pengajuan->metadata['form_data']['lama_perjalanan']);
        $this->assertStringContainsString('Bapak Budi (Kabid)', $pengajuan->metadata['form_data']['penandatangan']);
    }

    public function test_guest_cannot_access_pengajuan_surat(): void
    {
        $this->get(route('pengajuan-surat.index'))
            ->assertRedirect(route('login'));
    }

    public function test_surat_tugas_system_fields_ignore_manual_values_and_calculate_travel_dates(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-09-01',
                'perihal' => 'Pengajuan surat tugas dengan field sistem',
                'fields' => [
                    'nomor_surat' => 'NOMOR-MANUAL-TIDAK-BOLEH-TERPAKAI',
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan hutan',
                    'tujuan_perjalanan' => 'Kawasan Hutan Lindung',
                    'tanggal_mulai_perjalanan' => '2026-09-01',
                    'tanggal_selesai_perjalanan' => '2026-09-05',
                    'lama_perjalanan' => '999 hari',
                    'keterangan_biaya' => '-',
                    'kewajiban_laporan' => 'Membuat laporan tertulis setelah pelaksanaan tugas.',
                    'penandatangan' => 'Penandatangan palsu',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.index'));

        $formData = PengajuanSurat::firstOrFail()->metadata['form_data'];

        $this->assertStringStartsWith('800.1.11.1/001/ST/Dishut.III/', $formData['nomor_surat']);
        $this->assertSame('5 hari / 01/09/2026 s.d. 05/09/2026', $formData['lama_perjalanan']);
        $this->assertStringContainsString('Bapak Budi (Kabid)', $formData['penandatangan']);
    }

    public function test_surat_tugas_rejects_travel_end_date_before_start_date(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-09-01',
                'perihal' => 'Pengajuan surat tugas tanggal tidak valid',
                'fields' => [
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan hutan',
                    'tujuan_perjalanan' => 'Kawasan Hutan Lindung',
                    'tanggal_mulai_perjalanan' => '2026-09-05',
                    'tanggal_selesai_perjalanan' => '2026-09-01',
                    'keterangan_biaya' => '-',
                    'kewajiban_laporan' => 'Membuat laporan tertulis setelah pelaksanaan tugas.',
                    'penandatangan' => 'Penandatangan palsu',
                ],
            ])
            ->assertSessionHasErrors('fields.tanggal_selesai_perjalanan');

        $this->assertDatabaseMissing('pengajuan_surats', [
            'perihal' => 'Pengajuan surat tugas tanggal tidak valid',
        ]);
    }

    public function test_admin_cannot_create_pengajuan_surat(): void
    {
        $this->seed();

        $admin = User::where('role', 'admin')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-cuti')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('pengajuan-surat.create'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-24',
                'perihal' => 'Pengajuan cuti oleh admin',
                'fields' => [],
            ])
            ->assertForbidden();
    }

    public function test_only_staff_can_create_pengajuan_surat(): void
    {
        $this->seed();

        $kasi = User::where('role', 'kasi')->firstOrFail();
        $kabid = User::where('role', 'kabid')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-cuti')->firstOrFail();

        foreach ([$kasi, $kabid] as $user) {
            $this->actingAs($user)
                ->get(route('pengajuan-surat.create'))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('pengajuan-surat.store'), [
                    'jenis_surat_id' => $jenisSurat->id,
                    'tanggal_pengajuan' => '2026-07-24',
                    'perihal' => 'Pengajuan dari non-staff',
                    'fields' => [],
                ])
                ->assertForbidden();
        }
    }

    public function test_user_management_is_admin_only(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $kasi = User::where('role', 'kasi')->firstOrFail();
        $kabid = User::where('role', 'kabid')->firstOrFail();

        foreach ([$staff, $kasi, $kabid] as $user) {
            $this->actingAs($user)
                ->get(route('users.index'))
                ->assertForbidden();

            $this->actingAs($user)
                ->get(route('users.create'))
                ->assertForbidden();
        }
    }

    public function test_kabid_uses_pengajuan_workspace_instead_of_legacy_surat_modules(): void
    {
        $this->seed();

        $kabid = User::where('role', 'kabid')->firstOrFail();

        $this->actingAs($kabid)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Approval Kabid')
            ->assertDontSee('Monitoring Surat Masuk')
            ->assertDontSee('Surat Keluar');

        $this->actingAs($kabid)
            ->get(route('surat-masuk.index'))
            ->assertForbidden();

        $this->actingAs($kabid)
            ->get(route('surat-keluar.index'))
            ->assertForbidden();

        $this->actingAs($kabid)
            ->get(route('disposisi.index'))
            ->assertForbidden();
    }

    public function test_staff_uses_pengajuan_workspace_instead_of_legacy_surat_modules(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengajuan Saya')
            ->assertDontSee('Kotak Masuk Saya')
            ->assertDontSee('Surat Keluar');

        $this->actingAs($staff)
            ->get(route('surat-masuk.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('surat-keluar.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('disposisi.index'))
            ->assertForbidden();
    }

    public function test_staff_can_preview_and_export_pengajuan_template(): void
    {
        Storage::fake('local');
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'nota-dinas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-24',
                'perihal' => 'Nota dinas koordinasi internal',
                'fields' => [
                    'tembusan' => 'Sekretaris u.b Kasubbag. Perencanaan, Evaluasi dan Pelaporan',
                    'dari' => 'Kepala Bidang Perlindungan dan KSDAE',
                    'tanggal_nota' => '2026-07-24',
                    'lampiran' => UploadedFile::fake()->create('lampiran-nota.pdf', 100, 'application/pdf'),
                    'perihal_nota' => 'Koordinasi internal',
                    'isi_nota' => 'Permohonan koordinasi tindak lanjut kegiatan.',
                    'rincian_lampiran' => 'Daftar kegiatan',
                ],
            ]);

        $pengajuan = PengajuanSurat::firstOrFail();
        $formData = $pengajuan->metadata['form_data'];

        $this->assertSame('Kepala Dinas Kehutanan Provinsi Sumatera Selatan', $formData['kepada']);
        $this->assertStringStartsWith('500.0.0.0/001/ND.DISHUT/I/', $formData['nomor_nota']);
        $this->assertStringContainsString('Bapak Budi (Kabid)', $formData['penandatangan']);
        $this->assertSame('lampiran-nota.pdf', $formData['lampiran']['original_name']);
        Storage::disk('local')->assertExists($formData['lampiran']['path']);

        $this->get(route('pengajuan-surat.preview', $pengajuan))
            ->assertOk()
            ->assertSee('Nota Dinas')
            ->assertSee('Koordinasi internal')
            ->assertSee('lampiran-nota.pdf')
            ->assertSee('Download PDF')
            ->assertSee('Download DOCX');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $docxResponse = $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']));
        $docxResponse
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $documentXml = $this->documentXmlFromDocxResponse($docxResponse);
        $this->assertStringContainsString('NOTA DINAS', $documentXml);
        $this->assertStringContainsString('Koordinasi internal', $documentXml);
        $this->assertStringContainsString('Permohonan koordinasi tindak lanjut kegiatan.', $documentXml);
        $this->assertStringNotContainsString('Sistem E-Arsip Surat Digital', $documentXml);
    }

    public function test_surat_tugas_docx_export_uses_official_template_layout(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-24',
                'perihal' => 'Surat tugas berbasis template resmi',
                'fields' => [
                    'dasar_pertama' => 'Dasar pertama custom.',
                    'dasar_kedua' => 'Dasar kedua custom.',
                    'pegawai_berangkat' => "1. Rina Putri - NIP 198801012010012001 - Penata/III.c - Analis Kehutanan\n2. Budi Santoso - NIP 198902022011011002 - Penata Muda/III.a - Pengelola Data",
                    'kegiatan' => 'Menghadiri rapat koordinasi pemulihan kawasan.',
                    'tujuan_perjalanan' => 'Kantor Dinas Kehutanan Provinsi Sumatera Selatan',
                    'tanggal_mulai_perjalanan' => '2026-07-27',
                    'tanggal_selesai_perjalanan' => '2026-07-31',
                    'keterangan_biaya' => 'Biaya dibebankan pada kegiatan terkait.',
                    'kewajiban_laporan' => 'Membuat laporan setelah kegiatan.',
                    'penandatangan' => 'Kabid',
                ],
            ]);

        $pengajuan = PengajuanSurat::firstOrFail();
        $response = $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']));
        $documentXml = $this->documentXmlFromDocxResponse($response);

        $this->assertStringContainsString('PEMERINTAH PROVINSI SUMATERA SELATAN', $documentXml);
        $this->assertStringContainsString('SURAT ', $documentXml);
        $this->assertStringContainsString('TUGAS', $documentXml);
        $this->assertStringContainsString('Dasar pertama custom.', $documentXml);
        $this->assertStringContainsString('Rina Putri', $documentXml);
        $this->assertStringContainsString('Menghadiri rapat koordinasi pemulihan kawasan.', $documentXml);
        $this->assertStringContainsString('5 hari / 27/07/2026 s.d. 31/07/2026', $documentXml);
        $this->assertStringContainsString('Bapak Budi (Kabid)', $documentXml);
        $this->assertStringNotContainsString('Sistem E-Arsip Surat Digital', $documentXml);
    }

    public function test_nota_dinas_system_fields_ignore_manual_request_values(): void
    {
        Storage::fake('local');
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'nota-dinas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-08-12',
                'perihal' => 'Nota dinas otomatis',
                'fields' => [
                    'kepada' => 'Input manual tidak boleh dipakai',
                    'tembusan' => '-',
                    'dari' => 'Bidang Perlindungan dan KSDAE',
                    'tanggal_nota' => '2026-08-12',
                    'nomor_nota' => 'NOMOR PALSU',
                    'lampiran' => UploadedFile::fake()->create('lampiran-manual.pdf', 80, 'application/pdf'),
                    'perihal_nota' => 'Koordinasi',
                    'isi_nota' => 'Isi nota dinas.',
                    'rincian_lampiran' => '-',
                    'penandatangan' => 'Penandatangan palsu',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.index'));

        $formData = PengajuanSurat::firstOrFail()->metadata['form_data'];

        $this->assertSame('Kepala Dinas Kehutanan Provinsi Sumatera Selatan', $formData['kepada']);
        $this->assertStringStartsWith('500.0.0.0/001/ND.DISHUT/I/', $formData['nomor_nota']);
        $this->assertStringContainsString('Bapak Budi (Kabid)', $formData['penandatangan']);
        $this->assertNotSame('NOMOR PALSU', $formData['nomor_nota']);
        $this->assertNotSame('Penandatangan palsu', $formData['penandatangan']);
        $this->assertSame('lampiran-manual.pdf', $formData['lampiran']['original_name']);
        Storage::disk('local')->assertExists($formData['lampiran']['path']);
    }

    public function test_surat_cuti_uses_account_profile_quota_and_attachment_upload(): void
    {
        Storage::fake('local');
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-cuti')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-08-12',
                'perihal' => 'Pengajuan cuti tahunan',
                'fields' => [
                    'jenis_cuti' => 'Cuti tahunan',
                    'tanggal_mulai' => '2026-08-17',
                    'tanggal_selesai' => '2026-08-19',
                    'unit_kerja' => 'Dinas Kehutanan Provinsi Sumatera Selatan',
                    'alasan' => 'Keperluan keluarga',
                    'alamat_selama_cuti' => 'Palembang',
                    'telepon' => '081234567890',
                    'atasan_langsung' => 'Atasan palsu',
                    'lampiran' => UploadedFile::fake()->create('surat-pendukung.pdf', 120, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.index'));

        $pengajuan = PengajuanSurat::firstOrFail();
        $formData = $pengajuan->metadata['form_data'];

        $this->assertSame($staff->name, $formData['nama_pegawai']);
        $this->assertSame($staff->nip, $formData['nip']);
        $this->assertSame($staff->jabatan, $formData['jabatan_unit']);
        $this->assertSame('Ibu Siti (Kasi) - Kasi Rehabilitasi Hutan', $formData['atasan_langsung']);
        $this->assertArrayNotHasKey('masa_kerja', $formData);
        $this->assertSame('3 hari', $formData['lama_cuti']);
        $this->assertSame(3, $pengajuan->metadata['cuti_quota']['requested_days']);
        $this->assertSame(9, $pengajuan->metadata['cuti_quota']['remaining_days_after_request']);
        $this->assertSame('surat-pendukung.pdf', $formData['lampiran']['original_name']);
        Storage::disk('local')->assertExists($formData['lampiran']['path']);

        $this->get(route('pengajuan-surat.show', $pengajuan))
            ->assertOk()
            ->assertSee('surat-pendukung.pdf');

        $docxResponse = $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']));
        $documentXml = $this->documentXmlFromDocxResponse($docxResponse);
        $this->assertStringContainsString('FORMULIR PERMINTAAN DAN PEMBERIAN CUTI', $documentXml);
        $this->assertStringContainsString($staff->name, $documentXml);
        $this->assertStringContainsString('Keperluan keluarga', $documentXml);
        $this->assertStringContainsString('3 HARI', $documentXml);
        $this->assertStringNotContainsString('Sistem E-Arsip Surat Digital', $documentXml);
    }

    public function test_surat_cuti_rejects_request_that_exceeds_annual_quota(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-cuti')->firstOrFail();

        PengajuanSurat::create([
            'jenis_surat_id' => $jenisSurat->id,
            'pemohon_id' => $staff->id,
            'nomor_pengajuan' => 'PGJ-20260101-0001',
            'tanggal_pengajuan' => '2026-01-01',
            'perihal' => 'Cuti tahunan sebelumnya',
            'status' => 'selesai',
            'posisi_saat_ini' => $staff->id,
            'metadata' => [
                'form_data' => [
                    'jenis_cuti' => 'Cuti tahunan',
                    'tanggal_mulai' => '2026-01-06',
                    'tanggal_selesai' => '2026-01-15',
                    'lama_cuti' => '10 hari',
                ],
            ],
        ]);

        $this->actingAs($staff)
            ->from(route('pengajuan-surat.create'))
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-08-12',
                'perihal' => 'Pengajuan cuti tahunan melebihi kuota',
                'fields' => [
                    'jenis_cuti' => 'Cuti tahunan',
                    'tanggal_mulai' => '2026-08-17',
                    'tanggal_selesai' => '2026-08-19',
                    'unit_kerja' => 'Dinas Kehutanan Provinsi Sumatera Selatan',
                    'alasan' => 'Keperluan keluarga',
                    'alamat_selama_cuti' => 'Palembang',
                    'telepon' => '081234567890',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.create'))
            ->assertSessionHasErrors('fields.tanggal_selesai');
    }

    public function test_surat_cuti_rejects_single_request_longer_than_annual_quota(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-cuti')->firstOrFail();

        $this->actingAs($staff)
            ->from(route('pengajuan-surat.create'))
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-08-12',
                'perihal' => 'Pengajuan cuti tahunan 13 hari',
                'fields' => [
                    'jenis_cuti' => 'Cuti tahunan',
                    'tanggal_mulai' => '2026-08-01',
                    'tanggal_selesai' => '2026-08-13',
                    'unit_kerja' => 'Dinas Kehutanan Provinsi Sumatera Selatan',
                    'alasan' => 'Keperluan keluarga',
                    'alamat_selama_cuti' => 'Palembang',
                    'telepon' => '081234567890',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.create'))
            ->assertSessionHasErrors('fields.tanggal_selesai');

        $this->assertDatabaseMissing('pengajuan_surats', [
            'perihal' => 'Pengajuan cuti tahunan 13 hari',
        ]);
    }

    public function test_staff_can_create_surat_undangan_from_template_folder(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-undangan')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-30',
                'perihal' => 'Undangan rapat koordinasi KBEP',
                'fields' => [
                    'nomor_surat' => '500.4.6.4/3508/Dishut.III/2026',
                    'sifat' => 'Biasa',
                    'lampiran' => '-',
                    'hal' => 'Undangan Rapat',
                    'tujuan_undangan' => "1. Kepala UPTD KPH Wilayah VIII Semendo\n2. Direktur PT. Genus Rona Hijau",
                    'latar_belakang' => 'Dalam rangka percepatan pelaksanaan implementasi RBP REDD+ GCF Output II.',
                    'hari_tanggal' => 'Jumat / 31 Agustus 2026',
                    'waktu' => '10.00 WIB s.d Selesai',
                    'tempat' => 'Zoom meeting pada tautan https://bit.ly/Ranker-KBEP',
                    'meeting_id' => '897 5674 5132',
                    'passcode' => '629115',
                    'agenda' => 'Penyampaian Rencana Kerja Tenaga Ahli KBEP.',
                    'kontak_konfirmasi' => 'I Gusti Ayu Kusuma Wardani (0813-7391-4100)',
                    'penandatangan' => 'Drs. H. KOIMUDIN, S.H., M.M - Kepala Dinas',
                ],
            ])
            ->assertRedirect(route('pengajuan-surat.index'));

        $pengajuan = PengajuanSurat::where('jenis_surat_id', $jenisSurat->id)->firstOrFail();

        $this->get(route('pengajuan-surat.preview', $pengajuan))
            ->assertOk()
            ->assertSee('Surat Undangan Rapat')
            ->assertSee('Undangan Rapat')
            ->assertSee('20260729080454_Surat undangan Rapat Genus Rona_31 Agustus 2026.pdf');

        $docxResponse = $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']));
        $documentXml = $this->documentXmlFromDocxResponse($docxResponse);
        $this->assertStringContainsString('PEMERINTAH PROVINSI SUMATERA SELATAN', $documentXml);
        $this->assertStringContainsString('Undangan Rapat', $documentXml);
        $this->assertStringContainsString('Penyampaian Rencana Kerja Tenaga Ahli KBEP.', $documentXml);
        $this->assertStringNotContainsString('Sistem E-Arsip Surat Digital', $documentXml);
    }

    public function test_kabid_can_digitally_sign_approved_pengajuan(): void
    {
        Storage::fake('local');
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $kabid = User::where('role', 'kabid')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $pengajuan = PengajuanSurat::create([
            'jenis_surat_id' => $jenisSurat->id,
            'pemohon_id' => $staff->id,
            'nomor_pengajuan' => 'PGJ-20260724-9999',
            'tanggal_pengajuan' => '2026-07-24',
            'perihal' => 'Surat tugas untuk ditandatangani',
            'status' => 'disetujui_kabid',
            'posisi_saat_ini' => $kabid->id,
            'metadata' => [
                'form_data' => [
                    'nomor_surat' => '800.1.11.1/001/ST/Dishut.III/2026',
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan',
                    'tujuan_perjalanan' => 'Hutan Lindung',
                    'lama_perjalanan' => '2 (dua) hari / 25-26 Juli 2026',
                    'keterangan_biaya' => '-',
                    'kewajiban_laporan' => 'Melakukan pemantauan',
                    'penandatangan' => 'Penandatangan palsu',
                ],
            ],
        ]);

        $this->actingAs($kabid)
            ->post(route('pengajuan-surat.sign', $pengajuan))
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('signature_keys', [
            'user_id' => $kabid->id,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('digital_signatures', [
            'pengajuan_surat_id' => $pengajuan->id,
            'signer_id' => $kabid->id,
            'algorithm' => 'RSA-2048/SHA-512',
        ]);

        $signature = $pengajuan->fresh()->digitalSignature;

        $this->assertNotNull($signature->verification_code);
        Storage::disk('local')->assertExists($signature->metadata['file_paths']['pdf']);
        Storage::disk('local')->assertExists($signature->metadata['file_paths']['docx']);
        $signedPdf = Storage::disk('local')->get($signature->metadata['file_paths']['pdf']);

        $this->assertStringContainsString('Scan barcode', $signedPdf);
        $this->assertStringContainsString($signature->verification_code, $signedPdf);

        $this->actingAs($kabid)
            ->get(route('pengajuan-surat.show', $pengajuan))
            ->assertOk()
            ->assertSee($signature->verification_code);

        $this->actingAs($staff)
            ->get(route('pengajuan-surat.preview', $pengajuan))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertSee($signature->verification_code);

        $this->assertDatabaseHas('pengajuan_surats', [
            'id' => $pengajuan->id,
            'status' => 'selesai',
            'posisi_saat_ini' => $staff->id,
        ]);

        $this->assertDatabaseHas('riwayat_pengajuan_surats', [
            'pengajuan_surat_id' => $pengajuan->id,
            'actor_id' => $kabid->id,
            'target_user_id' => $staff->id,
            'aksi' => 'tandatangan_kabid',
            'status_sesudah' => 'selesai',
        ]);
    }

    public function test_public_can_verify_signed_document_by_code_and_uploaded_pdf(): void
    {
        Storage::fake('local');
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $kabid = User::where('role', 'kabid')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $pengajuan = PengajuanSurat::create([
            'jenis_surat_id' => $jenisSurat->id,
            'pemohon_id' => $staff->id,
            'nomor_pengajuan' => 'PGJ-20260725-0005',
            'tanggal_pengajuan' => '2026-07-25',
            'perihal' => 'Surat tugas untuk verifikasi publik',
            'status' => 'disetujui_kabid',
            'posisi_saat_ini' => $kabid->id,
            'metadata' => [
                'form_data' => [
                    'nomor_surat' => '800.1.11.1/002/ST/Dishut.III/2026',
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan',
                    'tujuan_perjalanan' => 'Hutan Lindung',
                    'lama_perjalanan' => '2 (dua) hari / 26-27 Juli 2026',
                    'keterangan_biaya' => '-',
                    'kewajiban_laporan' => 'Melakukan pemantauan',
                    'penandatangan' => 'Kabid',
                ],
            ],
        ]);

        $this->actingAs($kabid)
            ->post(route('pengajuan-surat.sign', $pengajuan))
            ->assertRedirect()
            ->assertSessionMissing('error');

        $signature = $pengajuan->fresh()->digitalSignature;
        $pdfPath = Storage::disk('local')->path($signature->metadata['file_paths']['pdf']);
        $uploadedPdf = new UploadedFile($pdfPath, 'dokumen-final.pdf', 'application/pdf', null, true);

        $this->get(route('verification.show', $signature->verification_code))
            ->assertOk()
            ->assertSee('Dokumen valid')
            ->assertSee($signature->verification_code);

        $this->post(route('verification.verify'), [
            'kode' => $signature->verification_code,
            'dokumen' => $uploadedPdf,
        ])
            ->assertOk()
            ->assertSee('Kode, tanda tangan digital, dan file upload cocok');

        $this->assertDatabaseHas('verification_logs', [
            'digital_signature_id' => $signature->id,
            'verification_code' => $signature->verification_code,
            'status' => 'valid',
        ]);
    }

    public function test_pengajuan_approval_flows_from_kasi_to_kabid(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $kasi = User::where('role', 'kasi')->firstOrFail();
        $kabid = User::where('role', 'kabid')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'surat-tugas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-25',
                'perihal' => 'Pengajuan alur approval',
                'fields' => [
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan',
                    'tujuan_perjalanan' => 'Hutan Lindung',
                    'tanggal_mulai_perjalanan' => '2026-07-26',
                    'tanggal_selesai_perjalanan' => '2026-07-27',
                    'keterangan_biaya' => '-',
                    'kewajiban_laporan' => 'Melakukan pemantauan',
                    'penandatangan' => 'Kabid',
                ],
            ]);

        $pengajuan = PengajuanSurat::firstOrFail();

        $this->actingAs($kasi)
            ->post(route('pengajuan-surat.process', $pengajuan), [
                'aksi' => 'periksa',
                'catatan' => 'Mulai diperiksa Kasi',
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('pengajuan_surats', [
            'id' => $pengajuan->id,
            'status' => 'diperiksa_kasi',
            'posisi_saat_ini' => $kasi->id,
        ]);

        $this->actingAs($kasi)
            ->post(route('pengajuan-surat.process', $pengajuan), [
                'aksi' => 'acc',
                'catatan' => 'Disetujui Kasi',
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('pengajuan_surats', [
            'id' => $pengajuan->id,
            'status' => 'disetujui_kasi',
            'posisi_saat_ini' => $kabid->id,
        ]);

        $this->actingAs($kabid)
            ->post(route('pengajuan-surat.process', $pengajuan), [
                'aksi' => 'periksa',
                'catatan' => 'Mulai diperiksa Kabid',
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->actingAs($kabid)
            ->post(route('pengajuan-surat.process', $pengajuan), [
                'aksi' => 'acc',
                'catatan' => 'Disetujui Kabid',
            ])
            ->assertRedirect()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('pengajuan_surats', [
            'id' => $pengajuan->id,
            'status' => 'disetujui_kabid',
            'posisi_saat_ini' => $kabid->id,
        ]);

        foreach (['diajukan', 'periksa_kasi', 'acc_kasi', 'periksa_kabid', 'acc_kabid'] as $action) {
            $this->assertDatabaseHas('riwayat_pengajuan_surats', [
                'pengajuan_surat_id' => $pengajuan->id,
                'aksi' => $action,
            ]);
        }
    }
}
