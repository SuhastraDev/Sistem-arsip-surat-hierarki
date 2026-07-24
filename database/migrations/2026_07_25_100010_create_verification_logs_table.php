<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('digital_signature_id')->nullable()->constrained('digital_signatures')->nullOnDelete();
            $table->string('verification_code', 20)->nullable();
            $table->string('status', 30);
            $table->string('uploaded_file_name')->nullable();
            $table->string('uploaded_file_hash', 128)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['verification_code', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};
