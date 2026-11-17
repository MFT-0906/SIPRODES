<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agregat harian per halaman. Sengaja tanpa IP, user agent, maupun hash pengunjung:
     * hanya angka. Baris dengan path "*" berisi total seluruh situs pada hari itu.
     */
    public function up(): void
    {
        Schema::create('statistik_kunjungan', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('path', 150);
            $table->unsignedInteger('jumlah_tayang')->default(0);
            $table->unsignedInteger('jumlah_pengunjung')->default(0);

            $table->unique(['tanggal', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('statistik_kunjungan');
    }
};
