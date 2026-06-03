<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilais', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('siswa_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mata_pelajarans')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('teachers')->cascadeOnDelete();
            $table->string('semester');
            $table->decimal('tugas', 5, 2)->default(0);
            $table->decimal('uts', 5, 2)->default(0);
            $table->decimal('uas', 5, 2)->default(0);
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->timestamps();
            $table->unique(['siswa_id', 'mapel_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilais');
    }
};
