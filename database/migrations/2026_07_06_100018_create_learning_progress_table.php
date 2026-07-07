<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori_materi')->restrictOnDelete();
            $table->unsignedInteger('total_dijawab')->default(0);
            $table->unsignedInteger('total_benar')->default(0);
            $table->unsignedInteger('total_salah')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'kategori_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_progress');
    }
};
