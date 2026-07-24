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
                'icon' => 'trophy',
                'warna_badge' => 'green',
                'syarat_type' => AchievementCriteriaType::TotalMenang,
                'syarat_value' => 1,
                'reward_poin' => 50,
                'urutan' => 1,
            ],
            [
                'kode' => 'PLAY_5',
                'nama' => 'Pemanasan',
                'deskripsi' => 'Diberikan setelah menyelesaikan 5 kali permainan.',
                'icon' => 'flame',
                'warna_badge' => 'orange',
                'syarat_type' => AchievementCriteriaType::TotalPermainan,
                'syarat_value' => 5,
                'reward_poin' => 75,
                'urutan' => 2,
            ],
            [
                'kode' => 'WIN_10',
                'nama' => 'Master Statistik',
                'deskripsi' => 'Diberikan setelah memenangkan 10 kali permainan.',
                'icon' => 'crown',
                'warna_badge' => 'purple',
                'syarat_type' => AchievementCriteriaType::TotalMenang,
                'syarat_value' => 10,
                'reward_poin' => 150,
                'urutan' => 3,
            ],
            [
                'kode' => 'ACCURACY_80',
                'nama' => 'Ahli Akurat',
                'deskripsi' => 'Diberikan saat akurasi jawaban keseluruhan mencapai minimal 80%.',
                'icon' => 'target',
                'warna_badge' => 'blue',
                'syarat_type' => AchievementCriteriaType::AkurasiKeseluruhan,
                'syarat_value' => 80,
                'reward_poin' => 100,
                'urutan' => 4,
            ],
            [
                'kode' => 'PLAY_25',
                'nama' => 'Pemain Setia',
                'deskripsi' => 'Diberikan setelah menyelesaikan 25 kali permainan.',
                'icon' => 'users',
                'warna_badge' => 'pink',
                'syarat_type' => AchievementCriteriaType::TotalPermainan,
                'syarat_value' => 25,
                'reward_poin' => 125,
                'urutan' => 5,
            ],
            [
                'kode' => 'STRATEGIS_MARGIN_10',
                'nama' => 'Strategis',
                'deskripsi' => 'Menang dengan selisih minimal 10 langkah lebih cepat.',
                'icon' => 'crown',
                'warna_badge' => 'amber',
                'syarat_type' => AchievementCriteriaType::SelisihKemenanganTerbesar,
                'syarat_value' => 10,
                'reward_poin' => 150,
                'urutan' => 6,
            ],
            [
                'kode' => 'LUCKY_SIX_10',
                'nama' => 'Keberuntungan',
                'deskripsi' => 'Dapatkan angka 6 sebanyak 10 kali dalam permainan.',
                'icon' => 'dice',
                'warna_badge' => 'turquoise',
                'syarat_type' => AchievementCriteriaType::TotalAngkaEnam,
                'syarat_value' => 10,
                'reward_poin' => 100,
                'urutan' => 7,
            ],
            [
                'kode' => 'LEGEND_PLAY_50',
                'nama' => 'Legenda Ular Tangga',
                'deskripsi' => 'Selesaikan 50 kali permainan.',
                'icon' => 'wreath',
                'warna_badge' => 'purple',
                'syarat_type' => AchievementCriteriaType::TotalPermainan,
                'syarat_value' => 50,
                'reward_poin' => 200,
                'urutan' => 8,
            ],
        ];

        foreach ($achievements as $achievement) {
            Achievement::updateOrCreate(
                ['kode' => $achievement['kode']],
                [
                    'nama' => $achievement['nama'],
                    'deskripsi' => $achievement['deskripsi'],
                    'icon' => $achievement['icon'],
                    'warna_badge' => $achievement['warna_badge'],
                    'syarat_type' => $achievement['syarat_type'],
                    'syarat_value' => $achievement['syarat_value'],
                    'reward_poin' => $achievement['reward_poin'],
                    'urutan' => $achievement['urutan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
