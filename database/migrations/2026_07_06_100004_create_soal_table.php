<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori_materi')->restrictOnDelete();
            $table->text('pertanyaan');
            $table->json('opsi_jawaban');
            $table->string('kunci_jawaban');
            $table->text('pembahasan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soal');
    }
};
