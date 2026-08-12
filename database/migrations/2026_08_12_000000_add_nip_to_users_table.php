<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('nip')->nullable()->after('name');
        });

        DB::table('users')->where('email', 'kabid@dishut.com')->update(['nip' => '197801012006041001']);
        DB::table('users')->where('email', 'kasi@dishut.com')->update(['nip' => '198203152010012002']);
        DB::table('users')->where('email', 'staf@dishut.com')->update(['nip' => '199909062025211021']);
        DB::table('users')->where('email', 'staf2@dishut.com')->update(['nip' => '199610142020122003']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('nip');
        });
    }
};
