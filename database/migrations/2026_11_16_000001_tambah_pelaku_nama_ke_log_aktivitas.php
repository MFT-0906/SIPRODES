<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Salinan nama dan email pelaku di setiap baris log. Bila akun admin kelak dihapus, kolom user_id menjadi
 * kosong, tetapi log tetap menunjukkan siapa yang melakukan tindakan itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->string('pelaku_nama', 200)->nullable()->after('user_id');
        });

        // Isi untuk baris log yang sudah ada.
        DB::table('users')->select(['id', 'name', 'email'])->orderBy('id')->each(function ($u) {
            DB::table('log_aktivitas')->where('user_id', $u->id)->update(['pelaku_nama' => mb_substr($u->name.' ('.$u->email.')', 0, 200)]);
        });
    }

    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropColumn('pelaku_nama');
        });
    }
};
