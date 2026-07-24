<?php

namespace App\Services;

use App\Models\PengajuanSurat;
use App\Models\RiwayatPengajuanSurat;
use App\Models\User;
use RuntimeException;

class PengajuanApprovalService
{
    public function process(PengajuanSurat $pengajuanSurat, User $actor, string $action, ?string $note = null): void
    {
        if ($action === 'periksa') {
            $this->startReview($pengajuanSurat, $actor, $note);

            return;
        }

        if ($action === 'acc') {
            $this->approve($pengajuanSurat, $actor, $note);

            return;
        }

        if ($action === 'revisi') {
            $this->returnForRevision($pengajuanSurat, $actor, $note);

            return;
        }

        if ($action === 'ditolak') {
            $this->reject($pengajuanSurat, $actor, $note);

            return;
        }

        if ($action === 'ajukan_ulang') {
            $this->resubmit($pengajuanSurat, $actor, $note);

            return;
        }

        throw new RuntimeException('Aksi approval tidak dikenali.');
    }

    private function startReview(PengajuanSurat $pengajuanSurat, User $actor, ?string $note): void
    {
        $this->ensureCurrentActor($pengajuanSurat, $actor);

        if ($actor->role === 'kasi' && $pengajuanSurat->status === 'diajukan') {
            $this->transition($pengajuanSurat, $actor, 'periksa_kasi', 'diperiksa_kasi', $actor->id, $note);

            return;
        }

        if ($actor->role === 'kabid' && $pengajuanSurat->status === 'disetujui_kasi') {
            $this->transition($pengajuanSurat, $actor, 'periksa_kabid', 'diperiksa_kabid', $actor->id, $note);

            return;
        }

        throw new RuntimeException('Pengajuan belum siap untuk mulai diperiksa oleh role Anda.');
    }

    private function approve(PengajuanSurat $pengajuanSurat, User $actor, ?string $note): void
    {
        $this->ensureCurrentActor($pengajuanSurat, $actor);

        if ($actor->role === 'kasi' && $pengajuanSurat->status === 'diperiksa_kasi') {
            $kabid = User::where('role', 'kabid')->first();

            if (! $kabid) {
                throw new RuntimeException('Data Kabid tidak ditemukan.');
            }

            $this->transition($pengajuanSurat, $actor, 'acc_kasi', 'disetujui_kasi', $kabid->id, $note);

            return;
        }

        if ($actor->role === 'kabid' && $pengajuanSurat->status === 'diperiksa_kabid') {
            $this->transition($pengajuanSurat, $actor, 'acc_kabid', 'disetujui_kabid', $actor->id, $note);

            return;
        }

        throw new RuntimeException('Pengajuan belum berada pada tahap ACC untuk role Anda.');
    }

    private function returnForRevision(PengajuanSurat $pengajuanSurat, User $actor, ?string $note): void
    {
        $this->ensureCurrentActor($pengajuanSurat, $actor);
        $this->ensureReviewer($actor);

        $this->transition($pengajuanSurat, $actor, 'revisi', 'draft', $pengajuanSurat->pemohon_id, $note ?: 'Dikembalikan untuk revisi.');
    }

    private function reject(PengajuanSurat $pengajuanSurat, User $actor, ?string $note): void
    {
        $this->ensureCurrentActor($pengajuanSurat, $actor);
        $this->ensureReviewer($actor);

        $this->transition($pengajuanSurat, $actor, 'ditolak', 'ditolak', null, $note ?: 'Pengajuan ditolak.');
    }

    private function resubmit(PengajuanSurat $pengajuanSurat, User $actor, ?string $note): void
    {
        if ($pengajuanSurat->pemohon_id !== $actor->id || $pengajuanSurat->status !== 'draft') {
            throw new RuntimeException('Hanya pemohon yang dapat mengajukan ulang revisi.');
        }

        $kasi = User::find($actor->parent_id);

        if (! $kasi) {
            throw new RuntimeException('Atasan Kasi untuk pemohon tidak ditemukan.');
        }

        $this->transition($pengajuanSurat, $actor, 'ajukan_ulang', 'diajukan', $kasi->id, $note ?: 'Pengajuan revisi dikirim ulang.');
    }

    private function transition(PengajuanSurat $pengajuanSurat, User $actor, string $action, string $newStatus, ?int $targetUserId, ?string $note): void
    {
        $oldStatus = $pengajuanSurat->status;

        $pengajuanSurat->update([
            'status' => $newStatus,
            'posisi_saat_ini' => $targetUserId,
        ]);

        RiwayatPengajuanSurat::create([
            'pengajuan_surat_id' => $pengajuanSurat->id,
            'actor_id' => $actor->id,
            'target_user_id' => $targetUserId,
            'aksi' => $action,
            'status_sebelum' => $oldStatus,
            'status_sesudah' => $newStatus,
            'catatan' => $note,
            'metadata' => [
                'actor_role' => $actor->role,
            ],
        ]);
    }

    private function ensureCurrentActor(PengajuanSurat $pengajuanSurat, User $actor): void
    {
        if ($pengajuanSurat->posisi_saat_ini !== $actor->id) {
            throw new RuntimeException('Pengajuan ini tidak sedang berada di meja Anda.');
        }
    }

    private function ensureReviewer(User $actor): void
    {
        if (! in_array($actor->role, ['kasi', 'kabid'])) {
            throw new RuntimeException('Hanya Kasi atau Kabid yang dapat melakukan aksi approval.');
        }
    }
}
