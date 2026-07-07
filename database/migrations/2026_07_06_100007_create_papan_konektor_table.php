<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papan_konektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('papan_id')->constrained('papan_permainan')->cascadeOnDelete();
            $table->enum('jenis', ['tangga', 'ular']);
            $table->unsignedInteger('posisi_awal');
            $table->unsignedInteger('posisi_akhir');
            $table->string('label')->nullable();
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->index(['papan_id', 'posisi_awal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('papan_konektor');
    }
};
