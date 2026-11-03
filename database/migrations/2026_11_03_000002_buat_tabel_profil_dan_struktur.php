<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dusun', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('profil_desa', function (Blueprint $table) {
            $table->id();
            $table->text('sejarah')->nullable();
            $table->text('visi')->nullable();
            $table->json('misi')->nullable();
            $table->boolean('visi_misi_resmi')->default(false);
            $table->string('alamat_wilayah')->nullable();
            $table->string('alamat')->nullable();
            $table->decimal('lintang', 10, 7)->nullable();
            $table->decimal('bujur', 10, 7)->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('jam_layanan')->nullable();
            $table->boolean('kontak_terverifikasi')->default(false);
            $table->unsignedInteger('penduduk')->nullable();
            $table->unsignedInteger('kk')->nullable();
            $table->decimal('luas_ha', 10, 2)->nullable();
            $table->unsignedSmallInteger('jumlah_dusun')->nullable();
            $table->string('mata_pencaharian')->nullable();
            $table->string('sumber_data')->nullable();
            $table->boolean('data_terverifikasi')->default(false);
            $table->boolean('perangkat_resmi')->default(false);
            $table->timestamps();
        });

        Schema::create('perangkat_desa', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->foreignId('jabatan_id')->constrained('jabatan')->restrictOnDelete();
            $table->string('foto_path')->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->boolean('tampil')->default(true);
            $table->boolean('contoh')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perangkat_desa');
        Schema::dropIfExists('profil_desa');
        Schema::dropIfExists('jabatan');
        Schema::dropIfExists('dusun');
    }
};
