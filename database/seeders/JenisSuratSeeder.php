<?php

namespace Database\Seeders;

use App\Models\JenisSurat;
use Illuminate\Database\Seeder;

class JenisSuratSeeder extends Seeder
{
    public function run(): void
    {
        $jenisSurats = [
            [
                'nama' => 'Surat Cuti',
                'slug' => 'surat-cuti',
                'deskripsi' => 'Pengajuan cuti pegawai dengan data jenis cuti, tanggal, alasan, dan alamat selama cuti.',
            ],
            [
                'nama' => 'Surat Tugas',
                'slug' => 'surat-tugas',
                'deskripsi' => 'Pengajuan penugasan pegawai dengan tujuan, lokasi, periode, dasar, dan uraian tugas.',
            ],
            [
                'nama' => 'Nota Dinas',
                'slug' => 'nota-dinas',
                'deskripsi' => 'Pengajuan nota dinas internal dengan perihal, tujuan, isi ringkas, prioritas, dan catatan.',
            ],
        ];

        foreach ($jenisSurats as $jenisSurat) {
            JenisSurat::updateOrCreate(
                ['slug' => $jenisSurat['slug']],
                $jenisSurat + ['is_active' => true]
            );
        }
    }
}
