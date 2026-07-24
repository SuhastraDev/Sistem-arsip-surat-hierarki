<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pengajuan_surats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pengajuan_surat_id')->constrained('pengajuan_surats')->cascadeOnDelete();
            $table->foreignId('actor_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('aksi');
            $table->string('status_sebelum')->nullable();
            $table->string('status_sesudah');
            $table->text('catatan')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['pengajuan_surat_id', 'created_at']);
            $table->index(['actor_id', 'aksi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengajuan_surats');
    }
};
