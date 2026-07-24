<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatPengajuanSurat extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengajuan_surat_id',
        'actor_id',
        'target_user_id',
        'aksi',
        'status_sebelum',
        'status_sesudah',
        'catatan',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function pengajuanSurat(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
