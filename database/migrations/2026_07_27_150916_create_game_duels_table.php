<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_duels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenger_id')->constrained('game_players')->cascadeOnDelete();
            $table->foreignId('opponent_id')->constrained('game_players')->cascadeOnDelete();
            $table->enum('status', ['waiting', 'finished'])->default('waiting');
            $table->foreignId('winner_id')->nullable()->constrained('game_players')->nullOnDelete();
            $table->foreignId('loser_id')->nullable()->constrained('game_players')->nullOnDelete();
            $table->integer('loser_penalty_roll')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('game_duel_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_duel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->integer('order')->default(1);
            $table->timestamps();
        });

        Schema::create('game_duel_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_duel_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('time_taken_ms')->nullable();
            $table->timestamps();
            
            $table->unique(['game_duel_id', 'game_player_id', 'soal_id'], 'duel_player_soal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_duel_answers');
        Schema::dropIfExists('game_duel_questions');
        Schema::dropIfExists('game_duels');
    }
};
