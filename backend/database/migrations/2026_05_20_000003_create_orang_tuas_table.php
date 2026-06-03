<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orang_tuas', function (Blueprint $table): void {
            $table->id();
            $table->string('nama_ayah');
            $table->string('no_hp_ayah', 20)->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('nama_ibu');
            $table->string('no_hp_ibu', 20)->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->text('alamat')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orang_tuas');
    }
};
