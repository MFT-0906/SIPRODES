<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apbdes_periode', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->string('periode', 100);
            $table->string('status', 10)->default('draf');
            $table->timestamp('diterbitkan_at')->nullable();
            $table->boolean('contoh')->default(false);
            $table->timestamps();
        });

        Schema::create('apbdes_rincian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('apbdes_periode')->cascadeOnDelete();
            $table->string('jenis', 20);
            $table->string('uraian', 200);
            $table->unsignedBigInteger('anggaran')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['periode_id', 'jenis']);
        });

        Schema::create('apbdes_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('periode_id')->constrained('apbdes_periode')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->string('keterangan_periode', 100)->nullable();
            $table->string('path')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();
            $table->foreignId('diunggah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apbdes_dokumen');
        Schema::dropIfExists('apbdes_rincian');
        Schema::dropIfExists('apbdes_periode');
    }
};
