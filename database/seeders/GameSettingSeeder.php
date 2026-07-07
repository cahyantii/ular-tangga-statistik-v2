<?php

namespace Database\Seeders;

use App\Enums\SettingType;
use App\Models\GameSetting;
use Illuminate\Database\Seeder;

class GameSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'correct_answer_point',
                'value' => '10',
                'type' => SettingType::Integer,
                'label' => 'Poin Jawaban Benar',
                'deskripsi' => 'Poin yang didapat pemain saat menjawab soal dengan benar.',
                'group' => 'Skor',
            ],
            [
                'key' => 'wrong_answer_penalty',
                'value' => '5',
                'type' => SettingType::Integer,
                'label' => 'Penalti Jawaban Salah',
                'deskripsi' => 'Poin yang dikurangi saat pemain menjawab soal dengan salah atau tidak menjawab hingga waktu habis. Disimpan sebagai nilai positif, dikurangkan oleh sistem.',
                'group' => 'Skor',
            ],
            [
                'key' => 'bonus_point',
                'value' => '20',
                'type' => SettingType::Integer,
                'label' => 'Poin Bonus',
                'deskripsi' => 'Poin yang didapat pemain saat mendarat di petak Bonus.',
                'group' => 'Skor',
            ],
            [
                'key' => 'tile_penalty_point',
                'value' => '5',
                'type' => SettingType::Integer,
                'label' => 'Penalti Petak Penalti/Mystery',
                'deskripsi' => 'Poin yang dikurangi saat mendarat di petak Penalti atau efek negatif dari petak Mystery. Terpisah dari penalti jawaban salah. Disimpan sebagai nilai positif, dikurangkan oleh sistem.',
                'group' => 'Skor',
            ],
            [
                'key' => 'win_point',
                'value' => '100',
                'type' => SettingType::Integer,
                'label' => 'Poin Kemenangan',
                'deskripsi' => 'Poin tambahan yang didapat pemain saat memenangkan permainan (mencapai petak Finish).',
                'group' => 'Skor',
            ],
            [
                'key' => 'question_timer_seconds',
                'value' => '30',
                'type' => SettingType::Integer,
                'label' => 'Durasi Timer Soal (detik)',
                'deskripsi' => 'Batas waktu bagi pemain untuk menjawab soal sebelum dianggap salah otomatis.',
                'group' => 'Timer',
            ],
            [
                'key' => 'reconnect_timeout_seconds',
                'value' => '60',
                'type' => SettingType::Integer,
                'label' => 'Timeout Reconnect Multiplayer (detik)',
                'deskripsi' => 'Batas waktu bagi pemain yang terputus koneksi untuk kembali sebelum dinyatakan forfeit (kalah WO).',
                'group' => 'Timer',
            ],
            [
                'key' => 'heartbeat_timeout_seconds',
                'value' => '15',
                'type' => SettingType::Integer,
                'label' => 'Batas Diam Heartbeat (detik)',
                'deskripsi' => 'Jika klien multiplayer tidak mengirim heartbeat selama durasi ini, pemain dianggap terputus koneksi dan permainan dijeda.',
                'group' => 'Timer',
            ],
            [
                'key' => 'room_waiting_expiry_minutes',
                'value' => '5',
                'type' => SettingType::Integer,
                'label' => 'Timeout Room Menunggu (menit)',
                'deskripsi' => 'Room Quick Match/Private yang belum terisi lawan dalam durasi ini otomatis dibatalkan (Abandoned).',
                'group' => 'Timer',
            ],
        ];

        foreach ($settings as $setting) {
            GameSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'label' => $setting['label'],
                    'deskripsi' => $setting['deskripsi'],
                    'group' => $setting['group'],
                ]
            );
        }
    }
}
