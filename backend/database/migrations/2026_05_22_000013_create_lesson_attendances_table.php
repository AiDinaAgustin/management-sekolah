<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_pelajarans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lesson_meeting_id')->constrained('pertemuan_pelajarans')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('students')->cascadeOnDelete();
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['lesson_meeting_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_pelajarans');
    }
};
