<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_signatures', function (Blueprint $table): void {
            $table->string('verification_code', 20)->nullable()->unique()->after('algorithm');
        });
    }

    public function down(): void
    {
        Schema::table('digital_signatures', function (Blueprint $table): void {
            $table->dropUnique(['verification_code']);
            $table->dropColumn('verification_code');
        });
    }
};
