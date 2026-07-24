<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SignatureKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'public_key',
        'encrypted_private_key',
        'algorithm',
        'is_active',
    ];

    protected $casts = [
        'encrypted_private_key' => 'encrypted',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function digitalSignatures(): HasMany
    {
        return $this->hasMany(DigitalSignature::class);
    }
}
