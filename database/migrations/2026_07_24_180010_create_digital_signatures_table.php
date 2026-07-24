<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_surat_id')->unique()->constrained('pengajuan_surats')->cascadeOnDelete();
            $table->foreignId('signature_key_id')->constrained('signature_keys')->restrictOnDelete();
            $table->foreignId('signer_id')->constrained('users')->restrictOnDelete();
            $table->string('document_hash', 128);
            $table->longText('signature');
            $table->text('public_key');
            $table->string('algorithm')->default('RSA-2048/SHA-512');
            $table->timestamp('signed_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['signer_id', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};
