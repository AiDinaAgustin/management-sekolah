<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pertemuan_pelajarans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')->constrained('jadwal_pelajarans')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedInteger('pertemuan_ke');
            $table->string('topik')->nullable();
            $table->text('catatan')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['schedule_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pertemuan_pelajarans');
    }
};
