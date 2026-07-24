<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('papan_permainan', function (Blueprint $table) {
            $table->text('deskripsi')->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        Schema::table('papan_permainan', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
        });
    }
};
