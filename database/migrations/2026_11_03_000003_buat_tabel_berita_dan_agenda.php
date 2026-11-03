<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->string('slug')->unique();
            $table->string('kategori', 50);
            $table->string('ringkasan', 500)->nullable();
            $table->longText('isi');
            $table->string('gambar_path')->nullable();
            $table->string('gambar_alt')->nullable();
            $table->date('tanggal');
            $table->string('status', 10)->default('draf');
            $table->foreignId('penulis_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('diterbitkan_at')->nullable();
            $table->boolean('contoh')->default(false);
            $table->timestamps();

            $table->index(['status', 'tanggal']);
            $table->index('kategori');
        });

        Schema::create('agenda', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 200);
            $table->date('tanggal');
            $table->time('waktu')->nullable();
            $table->string('lokasi')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('status', 10)->default('draf');
            $table->boolean('contoh')->default(false);
            $table->timestamps();

            $table->index(['status', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda');
        Schema::dropIfExists('berita');
    }
};
