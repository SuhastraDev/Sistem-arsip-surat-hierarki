<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PengajuanSurat extends Model
{
    use HasFactory;

    public const STATUS = [
        'draft' => 'Draft',
        'diajukan' => 'Diajukan',
        'diperiksa_kasi' => 'Diperiksa Kasi',
        'disetujui_kasi' => 'Disetujui Kasi',
        'diperiksa_kabid' => 'Diperiksa Kabid',
        'disetujui_kabid' => 'Disetujui Kabid',
        'ditolak' => 'Ditolak',
        'ditandatangani' => 'Ditandatangani',
        'selesai' => 'Selesai',
    ];

    protected $fillable = [
        'jenis_surat_id',
        'pemohon_id',
        'nomor_pengajuan',
        'tanggal_pengajuan',
        'perihal',
        'status',
        'posisi_saat_ini',
        'metadata',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'metadata' => 'array',
    ];

    public function jenisSurat(): BelongsTo
    {
        return $this->belongsTo(JenisSurat::class);
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemohon_id');
    }

    public function posisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posisi_saat_ini');
    }

    public function digitalSignature(): HasOne
    {
        return $this->hasOne(DigitalSignature::class);
    }

    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPengajuanSurat::class)->latest();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? ucfirst((string) $this->status);
    }

    public function getTahapLabelAttribute(): string
    {
        if ($this->status === 'draft' && $this->posisi?->id === $this->pemohon_id) {
            return 'Menunggu revisi pemohon';
        }

        if ($this->status === 'diajukan' && $this->posisi?->role === 'kasi') {
            return 'Menunggu pemeriksaan Kasi';
        }

        if ($this->status === 'diperiksa_kasi') {
            return 'Sedang diperiksa Kasi';
        }

        if ($this->status === 'disetujui_kasi' || ($this->status === 'diajukan' && $this->posisi?->role === 'kabid')) {
            return 'Menunggu pemeriksaan Kabid';
        }

        if ($this->status === 'diperiksa_kabid') {
            return 'Sedang diperiksa Kabid';
        }

        if ($this->status === 'disetujui_kabid') {
            return 'Menunggu tanda tangan Kabid';
        }

        if ($this->status === 'ditandatangani') {
            return 'Menunggu finalisasi dokumen';
        }

        return $this->status_label;
    }
}
