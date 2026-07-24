<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengajuan_surat_id',
        'signature_key_id',
        'signer_id',
        'document_hash',
        'signature',
        'public_key',
        'algorithm',
        'verification_code',
        'signed_at',
        'metadata',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function pengajuanSurat(): BelongsTo
    {
        return $this->belongsTo(PengajuanSurat::class);
    }

    public function signatureKey(): BelongsTo
    {
        return $this->belongsTo(SignatureKey::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }
}
