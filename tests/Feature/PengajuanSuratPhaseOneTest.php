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
                    'nomor_surat' => '800.1.11.1/001/ST/Dishut.III/2026',
                    'dasar_pertama' => 'Peraturan Gubernur Sumatera Selatan Nomor 48 Tahun 2016.',
                    'dasar_kedua' => 'Surat Kepala Bappeda Nomor 000.1.5/1517/Bappeda-IV/2026.',
                    'pegawai_berangkat' => 'Mas Asep - NIP 199909062025211021 - Staf Lapangan',
                    'kegiatan' => 'Monitoring kawasan hutan',
                    'tujuan_perjalanan' => 'Kawasan Hutan Lindung',
                    'lama_perjalanan' => '3 (tiga) hari / 25-27 Juli 2026',
                    'keterangan_biaya' => 'Biaya dibebankan pada kegiatan terkait.',
                    'kewajiban_laporan' => 'Membuat laporan tertulis setelah pelaksanaan tugas.',
                    'penandatangan' => 'Kepala Bidang Konservasi',
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
    }

    public function test_guest_cannot_access_pengajuan_surat(): void
    {
        $this->get(route('pengajuan-surat.index'))
            ->assertRedirect(route('login'));
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

    public function test_kabid_uses_pengajuan_workspace_instead_of_legacy_surat_modules(): void
    {
        $this->seed();

        $kabid = User::where('role', 'kabid')->firstOrFail();

        $this->actingAs($kabid)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Meja Approval')
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

    public function test_staff_can_preview_and_export_pengajuan_template(): void
    {
        $this->seed();

        $staff = User::where('role', 'staff')->firstOrFail();
        $jenisSurat = JenisSurat::where('slug', 'nota-dinas')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('pengajuan-surat.store'), [
                'jenis_surat_id' => $jenisSurat->id,
                'tanggal_pengajuan' => '2026-07-24',
                'perihal' => 'Nota dinas koordinasi internal',
                'fields' => [
                    'kepada' => 'Kepala Dinas Kehutanan Provinsi Sumatera Selatan',
                    'tembusan' => 'Sekretaris u.b Kasubbag. Perencanaan, Evaluasi dan Pelaporan',
                    'dari' => 'Kepala Bidang Perlindungan dan KSDAE',
                    'tanggal_nota' => '2026-07-24',
                    'nomor_nota' => '500.0.0.0/001/ND.DISHUT/I/2026',
                    'lampiran' => '1 (satu) berkas',
                    'perihal_nota' => 'Koordinasi internal',
                    'isi_nota' => 'Permohonan koordinasi tindak lanjut kegiatan.',
                    'rincian_lampiran' => 'Daftar kegiatan',
                    'penandatangan' => 'Kepala Bidang Perlindungan dan KSDAE',
                ],
            ]);

        $pengajuan = PengajuanSurat::firstOrFail();

        $this->get(route('pengajuan-surat.preview', $pengajuan))
            ->assertOk()
            ->assertSee('Nota Dinas')
            ->assertSee('Koordinasi internal')
            ->assertSee('Download PDF')
            ->assertSee('Download DOCX');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
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
                    'penandatangan' => 'Kabid',
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

        $this->actingAs($kabid)
            ->get(route('pengajuan-surat.show', $pengajuan))
            ->assertOk()
            ->assertSee($signature->verification_code);

        $this->actingAs($staff)
            ->get(route('pengajuan-surat.preview', $pengajuan))
            ->assertOk()
            ->assertSee('Pengesahan Digital')
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
                    'nomor_surat' => '800.1.11.1/003/ST/Dishut.III/2026',
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
