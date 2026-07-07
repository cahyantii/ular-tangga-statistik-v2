<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('papan_id')->constrained('papan_permainan')->cascadeOnDelete();
            $table->unsignedInteger('posisi');
            $table->enum('jenis_petak', [
                'start', 'finish', 'biasa', 'soal', 'tangga', 'ular', 'bonus', 'penalti', 'mystery',
            ]);
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_materi')->nullOnDelete();
            $table->string('label')->nullable();
            $table->string('icon')->nullable();
            $table->string('warna')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['papan_id', 'posisi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petak');
    }
};
