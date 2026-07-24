<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Nota Dinas');
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
                    'pegawai_ditugaskan' => 'Mas Asep',
                    'jabatan_unit' => 'Staf Lapangan',
                    'tujuan_penugasan' => 'Monitoring kawasan hutan',
                    'lokasi_tugas' => 'Kawasan Hutan Lindung',
                    'tanggal_mulai' => '2026-07-25',
                    'tanggal_selesai' => '2026-07-27',
                    'dasar_keperluan' => 'Agenda monitoring berkala',
                    'uraian_tugas' => 'Melakukan pencatatan kondisi lapangan',
                    'pemberi_tugas' => 'Kepala Bidang Konservasi',
                    'lampiran' => 'Jadwal kegiatan',
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

        $this->assertStringStartsWith('PGJ-20260724-', $pengajuan->nomor_pengajuan);
        $this->assertSame('Monitoring kawasan hutan', $pengajuan->metadata['form_data']['tujuan_penugasan']);
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
                    'perihal_nota' => 'Koordinasi internal',
                    'tujuan_penerima' => 'Kepala Seksi',
                    'tanggal_pengajuan_nota' => '2026-07-24',
                    'unit_pengaju' => 'Staf Administrasi',
                    'isi_ringkas' => 'Permohonan koordinasi tindak lanjut kegiatan.',
                    'lampiran' => 'Daftar kegiatan',
                    'prioritas' => 'Penting',
                    'catatan_tambahan' => 'Perlu dibahas minggu ini',
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

    public function test_kabid_can_digitally_sign_approved_pengajuan(): void
    {
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
                    'pegawai_ditugaskan' => 'Mas Asep',
                    'jabatan_unit' => 'Staf Lapangan',
                    'tujuan_penugasan' => 'Monitoring kawasan',
                    'lokasi_tugas' => 'Hutan Lindung',
                    'tanggal_mulai' => '2026-07-25',
                    'tanggal_selesai' => '2026-07-26',
                    'dasar_keperluan' => 'Agenda monitoring',
                    'uraian_tugas' => 'Melakukan pemantauan',
                    'pemberi_tugas' => 'Kabid',
                    'lampiran' => '-',
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

        $this->assertDatabaseHas('pengajuan_surats', [
            'id' => $pengajuan->id,
            'status' => 'ditandatangani',
            'posisi_saat_ini' => null,
        ]);
    }
}
