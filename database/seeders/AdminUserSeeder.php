<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('app.admin_email');
        $password = config('app.admin_password');

        if (! $email || ! $password) {
            $this->command->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum diatur di .env - AdminUserSeeder dilewati.');

            return;
        }

        $admin = User::withTrashed()->firstOrNew(['email' => $email]);
        $admin->fill([
            'name' => 'Administrator',
            'email' => $email,
            'password' => $password,
        ]);
        $admin->role = UserRole::Admin;
        $admin->email_verified_at = now();
        $admin->save();

        $this->command->info("Akun admin siap: {$email}");
    }
}
