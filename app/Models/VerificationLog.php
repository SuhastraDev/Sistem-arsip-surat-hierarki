<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_signature_id',
        'verification_code',
        'status',
        'uploaded_file_name',
        'uploaded_file_hash',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function digitalSignature(): BelongsTo
    {
        return $this->belongsTo(DigitalSignature::class);
    }
}
