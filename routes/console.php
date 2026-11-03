<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Anonimisasi aspirasi yang sudah selesai melewati masa simpan (config/desa.php).
Schedule::command('aspirasi:anonimkan')->dailyAt('02:00')->onOneServer()->withoutOverlapping();

// Pemeliharaan: hapus log lama dan berkas unggahan yatim (lebih dari 24 jam, tidak dirujuk database).
Schedule::command('log:bersihkan')->monthlyOn(1, '03:00')->onOneServer();
Schedule::command('unggahan:bersihkan --hapus')->weeklyOn(0, '03:30')->onOneServer();
