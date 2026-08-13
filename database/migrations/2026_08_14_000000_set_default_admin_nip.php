<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@dishut.com')
            ->whereNull('nip')
            ->update(['nip' => '000000000000000000']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@dishut.com')
            ->where('nip', '000000000000000000')
            ->update(['nip' => null]);
    }
};
