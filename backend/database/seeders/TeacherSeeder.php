<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $guruUserId = DB::table('users')->where('email', 'guru@sekolah.test')->value('id');

        DB::table('teachers')->updateOrInsert(
            ['nip' => '1988001001'],
            [
                'user_id' => $guruUserId,
                'nama' => 'Guru Demo',
                'email' => 'guru@sekolah.test',
                'no_hp' => '081234567890',
                'alamat' => 'Jl. Pendidikan No. 1',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
