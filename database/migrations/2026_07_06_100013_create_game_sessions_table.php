<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->foreignId('papan_id')->constrained('papan_permainan')->restrictOnDelete();
            $table->enum('mode', ['vs_robot', 'multiplayer']);
            $table->enum('status', ['waiting', 'playing', 'paused', 'finished', 'abandoned']);
            $table->foreignId('active_question_id')->nullable()->constrained('soal')->nullOnDelete();
            $table->timestamp('active_question_expires_at')->nullable();
            $table->enum('win_reason', ['finish', 'forfeit'])->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('random_seed')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('total_turn')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_sessions');
    }
};
