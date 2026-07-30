<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dummyUsers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'avatar' => 'avatar1',
            ],
            [
                'name' => 'Siti Rahma',
                'email' => 'siti@example.com',
                'avatar' => 'avatar2',
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'ahmad@example.com',
                'avatar' => 'avatar3',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'avatar' => 'avatar4',
            ],
            [
                'name' => 'Eko Prasetyo',
                'email' => 'eko@example.com',
                'avatar' => 'avatar5',
            ],
            [
                'name' => 'Fitriani',
                'email' => 'fitri@example.com',
                'avatar' => 'avatar6',
            ],
        ];

        foreach ($dummyUsers as $userData) {
            $user = User::withTrashed()->firstOrNew(['email' => $userData['email']]);
            $user->fill([
                'name' => $userData['name'],
                'password' => Hash::make('password'),
                'avatar' => $userData['avatar'],
                'email_verified_at' => now(),
            ]);
            $user->role = UserRole::Player;
            $user->save();
        }

        $this->command->info('6 akun user dummy berhasil dibuat (password: password)');
    }
}
