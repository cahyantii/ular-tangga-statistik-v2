<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('game:abandon-stale-sessions')->hourly();

/**
 * Sub-menit (Tahap 12a) — cron biasa minimal 1 menit, jadi produksi harus
 * menjalankan `php artisan schedule:work` (proses panjang, sama seperti
 * Reverb/queue worker), bukan cron sekali per menit, supaya deteksi
 * disconnect multiplayer terasa dekat dengan real-time.w
 */
Schedule::command('game:check-heartbeats')->everyTenSeconds()->withoutOverlapping();

Schedule::command('game:expire-waiting-rooms')->everyMinute()->withoutOverlapping();

/**
 * Hosting shared tidak bisa menjalankan `queue:work` sebagai proses permanen,
 * jadi job queue (mis. email notifikasi login) diproses lewat cron per-menit
 * yang sama dengan `schedule:run`, bukan worker yang berjalan terus-menerus.
 */
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=1')
    ->everyMinute()
    ->withoutOverlapping();
