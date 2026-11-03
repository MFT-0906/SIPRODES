<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Anonimisasi aspirasi yang sudah selesai melewati masa simpan (config/desa.php).
Schedule::command('aspirasi:anonimkan')->dailyAt('02:00')->onOneServer()->withoutOverlapping();
