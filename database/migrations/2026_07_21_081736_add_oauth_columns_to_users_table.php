<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('avatar');
            $table->string('github_id')->nullable()->unique()->after('google_id');
        });

        // Password wajib pada skema saat ini (lihat migrasi awal users) - akun
        // OAuth diberi password acak saat dibuat (lihat SocialAuthService),
        // jadi kolom ini tetap NOT NULL, tidak perlu diubah jadi nullable.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'github_id']);
        });
    }
};
