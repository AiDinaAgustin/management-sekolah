<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomSeeder extends Seeder
{
    public function run(): void
    {
        $teacherId = DB::table('teachers')->where('email', 'guru@sekolah.test')->value('id');
        $academicYearId = DB::table('tahun_ajarans')->where('status_aktif', true)->value('id');

        DB::table('kelas')->insert([
            [
                'tingkat' => 7,
                'rombel' => 'A',
                'nama_kelas' => '7A',
                'wali_kelas_id' => $teacherId,
                'tahun_ajaran_id' => $academicYearId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'tingkat' => 7,
                'rombel' => 'B',
                'nama_kelas' => '7B',
                'wali_kelas_id' => $teacherId,
                'tahun_ajaran_id' => $academicYearId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
