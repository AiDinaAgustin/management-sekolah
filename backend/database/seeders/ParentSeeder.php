<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('orang_tuas')->insert([
            [
                'nama_ayah' => 'Budi Santoso',
                'no_hp_ayah' => '081200000001',
                'pekerjaan_ayah' => 'Wiraswasta',
                'nama_ibu' => 'Siti Aminah',
                'no_hp_ibu' => '081200000002',
                'pekerjaan_ibu' => 'Ibu Rumah Tangga',
                'alamat' => 'Jl. Melati No. 10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_ayah' => 'Andi Pratama',
                'no_hp_ayah' => '081200000003',
                'pekerjaan_ayah' => 'ASN',
                'nama_ibu' => 'Rina Maharani',
                'no_hp_ibu' => '081200000004',
                'pekerjaan_ibu' => 'Guru',
                'alamat' => 'Jl. Anggrek No. 5',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
