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
            ->assertSee('Koordinasi internal');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'html']))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'pdf']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('pengajuan-surat.export', [$pengajuan, 'docx']))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
