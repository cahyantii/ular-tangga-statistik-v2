<?php

namespace Database\Seeders;

use App\Enums\AchievementCriteriaType;
use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            [
                'kode' => 'FIRST_WIN',
                'nama' => 'Kemenangan Pertama',
                'deskripsi' => 'Diberikan setelah memenangkan permainan untuk pertama kalinya.',
                'syarat_type' => AchievementCriteriaType::TotalMenang,
                'syarat_value' => 1,
                'urutan' => 1,
            ],
            [
                'kode' => 'PLAY_5',
                'nama' => 'Pemanasan',
                'deskripsi' => 'Diberikan setelah menyelesaikan 5 kali permainan.',
                'syarat_type' => AchievementCriteriaType::TotalPermainan,
                'syarat_value' => 5,
                'urutan' => 2,
            ],
            [
                'kode' => 'WIN_10',
                'nama' => 'Master Statistik',
                'deskripsi' => 'Diberikan setelah memenangkan 10 kali permainan.',
                'syarat_type' => AchievementCriteriaType::TotalMenang,
                'syarat_value' => 10,
                'urutan' => 3,
            ],
            [
                'kode' => 'ACCURACY_80',
                'nama' => 'Ahli Akurat',
                'deskripsi' => 'Diberikan saat akurasi jawaban keseluruhan mencapai minimal 80%.',
                'syarat_type' => AchievementCriteriaType::AkurasiKeseluruhan,
                'syarat_value' => 80,
                'urutan' => 4,
            ],
            [
                'kode' => 'PLAY_25',
                'nama' => 'Pemain Setia',
                'deskripsi' => 'Diberikan setelah menyelesaikan 25 kali permainan.',
                'syarat_type' => AchievementCriteriaType::TotalPermainan,
                'syarat_value' => 25,
                'urutan' => 5,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['kode' => $achievement['kode']],
                [
                    'nama' => $achievement['nama'],
                    'deskripsi' => $achievement['deskripsi'],
                    'syarat_type' => $achievement['syarat_type'],
                    'syarat_value' => $achievement['syarat_value'],
                    'urutan' => $achievement['urutan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
