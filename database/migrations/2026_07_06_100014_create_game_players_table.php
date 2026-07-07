<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_session_id')->constrained('game_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_robot')->default(false);
            $table->unsignedTinyInteger('turn_order');
            $table->string('pawn_color');
            $table->string('pawn_icon')->nullable();
            $table->unsignedInteger('posisi_pion')->default(0);
            $table->integer('skor')->default(0);
            $table->decimal('accuracy', 5, 2)->nullable();
            $table->enum('status', ['active', 'disconnected'])->default('active');
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->timestamps();

            $table->index('game_session_id');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_players');
    }
};
