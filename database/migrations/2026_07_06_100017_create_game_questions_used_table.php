<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_questions_used', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('soal_id')->constrained('soal')->cascadeOnDelete();
            $table->timestamp('used_at')->useCurrent();

            $table->unique(['game_session_id', 'soal_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_questions_used');
    }
};
