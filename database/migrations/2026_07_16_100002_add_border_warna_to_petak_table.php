<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('petak', function (Blueprint $table) {
            $table->string('border_warna')->nullable()->after('warna');
        });
    }

    public function down(): void
    {
        Schema::table('petak', function (Blueprint $table) {
            $table->dropColumn('border_warna');
        });
    }
};
