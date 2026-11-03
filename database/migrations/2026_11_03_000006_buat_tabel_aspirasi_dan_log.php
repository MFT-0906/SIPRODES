<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aspirasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->nullable();
            // Terenkripsi (cast `encrypted`), sehingga memakai text. Tanpa IP mentah.
            $table->text('nik')->nullable();
            $table->text('pesan');
            $table->string('status', 10)->default('baru');
            $table->timestamp('dibaca_at')->nullable();
            $table->timestamp('selesai_at')->nullable();
            $table->timestamp('dianonimkan_at')->nullable();
            $table->boolean('contoh')->default(false);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('aspirasi_riwayat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspirasi_id')->constrained('aspirasi')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 50);
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('aksi', 50);
            $table->nullableMorphs('subjek');
            $table->string('keterangan')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('aksi');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
        Schema::dropIfExists('aspirasi_riwayat');
        Schema::dropIfExists('aspirasi');
    }
};
