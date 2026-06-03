<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('siswa_id')->constrained('students')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->foreignId('guru_id')->constrained('teachers')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['siswa_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
