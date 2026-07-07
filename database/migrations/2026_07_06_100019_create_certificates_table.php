<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jenis_sertifikat');
            $table->string('judul');
            $table->string('nomor_sertifikat')->unique();
            $table->string('verification_code')->unique();
            $table->string('template_path')->nullable();
            $table->string('file_path');
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
