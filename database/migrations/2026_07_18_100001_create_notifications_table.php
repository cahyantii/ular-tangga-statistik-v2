<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel notification standar Laravel (Illuminate\Notifications\DatabaseNotification),
 * dengan satu penyesuaian sengaja: kolom `data` bertipe `json` (bukan `text` seperti
 * skema bawaan `notifications:table`) supaya field di dalamnya (category, game_id,
 * room_id, achievement_id, dst - lihat App\Notifications\AppNotification) bisa
 * difilter lewat query JSON (`data->category`) di Notification Center admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
