<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komoditas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->string('musim_panen', 100)->nullable();
            $table->string('foto_path')->nullable();
            $table->string('foto_alt')->nullable();
            $table->boolean('unggulan')->default(false);
            $table->string('status', 10)->default('draf');
            $table->boolean('contoh')->default(false);
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('kelompok_tani', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->foreignId('komoditas_id')->nullable()->constrained('komoditas')->nullOnDelete();
            $table->foreignId('dusun_id')->nullable()->constrained('dusun')->nullOnDelete();
            $table->string('ketua', 100)->nullable();
            $table->string('kontak', 30)->nullable();
            $table->boolean('kontak_disetujui')->default(false);
            $table->boolean('contoh')->default(false);
            $table->timestamps();
        });

        Schema::create('umkm', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('produk', 200);
            $table->text('deskripsi')->nullable();
            $table->string('foto_path')->nullable();
            $table->string('foto_alt')->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->boolean('whatsapp_disetujui')->default(false);
            $table->string('status', 10)->default('draf');
            $table->boolean('contoh')->default(false);
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umkm');
        Schema::dropIfExists('kelompok_tani');
        Schema::dropIfExists('komoditas');
    }
};
