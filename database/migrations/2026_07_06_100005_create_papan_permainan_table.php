<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('papan_permainan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('jumlah_petak');
            $table->unsignedInteger('jumlah_kolom')->default(10);
            $table->string('thumbnail')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('papan_permainan');
    }
};
