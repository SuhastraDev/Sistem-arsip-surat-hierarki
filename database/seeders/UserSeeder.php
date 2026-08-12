<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. BUAT ADMIN
        User::create([
            'name' => 'Administrator',
            'nip' => null,
            'email' => 'admin@dishut.com',
            'password' => Hash::make('password'), // passwordnya: password
            'role' => 'admin',
            'jabatan' => 'Administrator Sistem',
        ]);

        // 2. BUAT KABID (Kepala Bidang)
        $kabid = User::create([
            'name' => 'Bapak Budi (Kabid)',
            'nip' => '197801012006041001',
            'email' => 'kabid@dishut.com',
            'password' => Hash::make('password'),
            'role' => 'kabid',
            'jabatan' => 'Kepala Bidang Konservasi',
        ]);

        // 3. BUAT KASI (Kepala Seksi) - Bawahan Kabid
        // Perhatikan 'parent_id' mengambil id dari $kabid
        $kasi = User::create([
            'name' => 'Ibu Siti (Kasi)',
            'nip' => '198203152010012002',
            'email' => 'kasi@dishut.com',
            'password' => Hash::make('password'),
            'role' => 'kasi',
            'parent_id' => $kabid->id, // INI KUNCI HIERARKINYA
            'jabatan' => 'Kasi Rehabilitasi Hutan',
        ]);

        // 4. BUAT STAF - Bawahan Kasi
        // Perhatikan 'parent_id' mengambil id dari $kasi
        User::create([
            'name' => 'Mas Asep (Staf)',
            'nip' => '199909062025211021',
            'email' => 'staf@dishut.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'parent_id' => $kasi->id, // INI KUNCI HIERARKINYA
            'jabatan' => 'Staf Lapangan',
        ]);

        // Buat satu staf lagi biar ramai
        User::create([
            'name' => 'Mba Dewi (Staf)',
            'nip' => '199610142020122003',
            'email' => 'staf2@dishut.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'parent_id' => $kasi->id,
            'jabatan' => 'Staf Administrasi',
        ]);
    }
}
