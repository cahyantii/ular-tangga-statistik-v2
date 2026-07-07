<?php

namespace App\Repositories\Admin;

use App\Enums\GameLogEventType;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Models\GameLog;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\KategoriMateri;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Query mentah untuk kebutuhan statistik Admin Dashboard.
 *
 * Kontrak payload GameLog untuk event AnswerSubmitted (ditulis oleh
 * QuestionService/ScoreService pada tahap Game Engine) yang menjadi
 * sumber data statistik soal di kelas ini:
 * [
 *     'soal_id' => int,
 *     'kategori_id' => int,
 *     'is_correct' => bool,
 * ]
 */
class AdminStatsRepository
{
    public function totalPemain(): array
    {
        $totalPemain = User::query()->where('role', 'player')->count();
        $pemainBaru7Hari = User::query()
            ->where('role', 'player')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        return [
            'total_pemain' => $totalPemain,
            'pemain_baru_7_hari' => $pemainBaru7Hari,
        ];
    }

    public function totalPermainan(): array
    {
        $totalSesi = GameSession::query()->count();
        $sesiSelesai = GameSession::query()->where('status', GameStatus::Finished->value)->count();
        $sesiBerlangsung = GameSession::query()
            ->whereIn('status', [GameStatus::Playing->value, GameStatus::Paused->value])
            ->count();

        $distribusiMode = GameSession::query()
            ->where('status', GameStatus::Finished->value)
            ->select('mode', DB::raw('COUNT(*) as total'))
            ->groupBy('mode')
            ->pluck('total', 'mode');

        return [
            'total_sesi' => $totalSesi,
            'sesi_selesai' => $sesiSelesai,
            'sesi_berlangsung' => $sesiBerlangsung,
            'total_vs_robot' => (int) ($distribusiMode[GameMode::VsRobot->value] ?? 0),
            'total_multiplayer' => (int) ($distribusiMode[GameMode::Multiplayer->value] ?? 0),
        ];
    }

    public function gamesPerHari(int $hari = 7): Collection
    {
        $mulai = now()->subDays($hari - 1)->startOfDay();

        $rows = GameSession::query()
            ->where('status', GameStatus::Finished->value)
            ->where('finished_at', '>=', $mulai)
            ->select(DB::raw('DATE(finished_at) as tanggal'), DB::raw('COUNT(*) as total'))
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal');

        $hasil = collect();
        for ($i = $hari - 1; $i >= 0; $i--) {
            $tanggal = now()->subDays($i)->toDateString();
            $hasil->put($tanggal, (int) ($rows[$tanggal] ?? 0));
        }

        return $hasil;
    }

    public function statistikSoal(): array
    {
        $totalSoal = Soal::query()->active()->count();

        $jawabanBenar = (int) $this->answerLogQuery()
            ->whereRaw("JSON_EXTRACT(payload, '$.is_correct') = true")
            ->count();
        $totalJawaban = (int) $this->answerLogQuery()->count();

        $rataRataAkurasi = $totalJawaban > 0
            ? round(($jawabanBenar / $totalJawaban) * 100, 2)
            : 0.0;

        return [
            'total_soal' => $totalSoal,
            'total_jawaban' => $totalJawaban,
            'rata_rata_akurasi' => $rataRataAkurasi,
        ];
    }

    public function akurasiPerKategori(): Collection
    {
        return KategoriMateri::query()
            ->active()
            ->orderBy('urutan')
            ->get()
            ->map(function (KategoriMateri $kategori) {
                $query = $this->answerLogQuery()
                    ->whereRaw("JSON_EXTRACT(payload, '$.kategori_id') = ?", [$kategori->id]);

                $total = (int) (clone $query)->count();
                $benar = (int) (clone $query)
                    ->whereRaw("JSON_EXTRACT(payload, '$.is_correct') = true")
                    ->count();

                return [
                    'kategori' => $kategori->nama,
                    'total_jawaban' => $total,
                    'akurasi' => $total > 0 ? round(($benar / $total) * 100, 2) : 0.0,
                ];
            });
    }

    public function soalTersulit(int $limit = 10): Collection
    {
        $rows = $this->answerLogQuery()
            ->select(
                DB::raw("JSON_EXTRACT(payload, '$.soal_id') as soal_id"),
                DB::raw('COUNT(*) as total_dijawab'),
                DB::raw("SUM(CASE WHEN JSON_EXTRACT(payload, '$.is_correct') = true THEN 0 ELSE 1 END) as total_salah")
            )
            ->groupBy('soal_id')
            ->having('total_dijawab', '>=', 1)
            ->orderByDesc('total_salah')
            ->limit($limit)
            ->get();

        $soalIds = $rows->pluck('soal_id')->map(fn ($id) => (int) $id);
        $soalMap = Soal::withTrashed()->whereIn('id', $soalIds)->get()->keyBy('id');

        return $rows->map(function ($row) use ($soalMap) {
            $soalId = (int) $row->soal_id;
            $soal = $soalMap->get($soalId);
            $totalDijawab = (int) $row->total_dijawab;
            $totalSalah = (int) $row->total_salah;

            return [
                'soal_id' => $soalId,
                'pertanyaan' => $soal?->pertanyaan ?? '(soal telah dihapus)',
                'total_dijawab' => $totalDijawab,
                'total_salah' => $totalSalah,
                'tingkat_kesalahan' => $totalDijawab > 0
                    ? round(($totalSalah / $totalDijawab) * 100, 2)
                    : 0.0,
            ];
        });
    }

    public function leaderboardRingkas(int $limit = 5): Collection
    {
        return GamePlayer::query()
            ->join('game_sessions', 'game_sessions.id', '=', 'game_players.game_session_id')
            ->where('game_sessions.status', GameStatus::Finished->value)
            ->where('game_players.is_robot', false)
            ->whereNotNull('game_players.user_id')
            ->select(
                'game_players.user_id',
                DB::raw('SUM(game_players.skor) as total_skor'),
                DB::raw("SUM(CASE WHEN game_sessions.winner_game_player_id = game_players.id THEN 1 ELSE 0 END) as total_menang")
            )
            ->groupBy('game_players.user_id')
            ->orderByDesc('total_skor')
            ->orderByDesc('total_menang')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $user = User::withTrashed()->find($row->user_id);

                return [
                    'nama' => $user?->name ?? '(pengguna dihapus)',
                    'total_skor' => (int) $row->total_skor,
                    'total_menang' => (int) $row->total_menang,
                ];
            });
    }

    private function answerLogQuery()
    {
        return GameLog::query()->where('event_type', GameLogEventType::AnswerSubmitted->value);
    }
}
