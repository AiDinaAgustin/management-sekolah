<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $classAId = DB::table('kelas')->where('nama_kelas', '7A')->value('id');
        $classBId = DB::table('kelas')->where('nama_kelas', '7B')->value('id');
        $parents = DB::table('orang_tuas')->pluck('id')->values();

        DB::table('students')->insert([
            [
                'nis' => '20260001',
                'nama_lengkap' => 'Ahmad Fauzan',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '2013-02-14',
                'alamat' => 'Jl. Melati No. 10',
                'kelas_id' => $classAId,
                'orang_tua_id' => $parents[0] ?? null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nis' => '20260002',
                'nama_lengkap' => 'Nabila Putri',
                'jenis_kelamin' => 'P',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '2013-07-02',
                'alamat' => 'Jl. Anggrek No. 5',
                'kelas_id' => $classAId,
                'orang_tua_id' => $parents[1] ?? null,
                'status_aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nis' => '20260003',
                'nama_lengkap' => 'Raka Pradana',
                'jenis_kelamin' => 'L',
                'tempat_lahir' => 'Depok',
                'tanggal_lahir' => '2012-11-19',
                'alamat' => 'Jl. Flamboyan No. 2',
                'kelas_id' => $classBId,
                'orang_tua_id' => $parents[0] ?? null,
                'status_aktif' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
