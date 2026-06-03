<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_sekolah');
            $table->string('tagline')->nullable();
            $table->timestamps();
        });

        DB::table('school_profiles')->insert([
            'nama_sekolah' => 'School Admin',
            'tagline' => 'Sistem Informasi Sekolah',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
    }
};
