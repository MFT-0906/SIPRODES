<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('email');
            $table->string('peran', 20)->default('staf')->after('password');
            $table->boolean('aktif')->default(true)->after('peran');
            $table->timestamp('terakhir_masuk_at')->nullable()->after('aktif');
        });

        Schema::create('modul_akses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('modul', 30);
            $table->timestamps();

            $table->unique(['user_id', 'modul']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_akses');

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn(['username', 'peran', 'aktif', 'terakhir_masuk_at']);
        });
    }
};
