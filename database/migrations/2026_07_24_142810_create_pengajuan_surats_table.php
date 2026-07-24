<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_surats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('jenis_surat_id')->constrained('jenis_surats')->restrictOnDelete();
            $table->foreignId('pemohon_id')->constrained('users')->cascadeOnDelete();
            $table->string('nomor_pengajuan')->unique();
            $table->date('tanggal_pengajuan');
            $table->text('perihal');
            $table->enum('status', [
                'draft',
                'diajukan',
                'diperiksa_kasi',
                'disetujui_kasi',
                'diperiksa_kabid',
                'disetujui_kabid',
                'ditolak',
                'ditandatangani',
                'selesai',
            ])->default('draft');
            $table->foreignId('posisi_saat_ini')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal_pengajuan']);
            $table->index(['pemohon_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surats');
    }
};
