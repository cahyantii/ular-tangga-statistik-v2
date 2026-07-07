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
 * disconnect multiplayer terasa dekat dengan real-time.
 */
Schedule::command('game:check-heartbeats')->everyTenSeconds()->withoutOverlapping();

Schedule::command('game:expire-waiting-rooms')->everyMinute()->withoutOverlapping();
