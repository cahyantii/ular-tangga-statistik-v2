<?php

namespace App\Services\Game;

use App\Enums\GameLogEventType;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PlayerStatus;
use App\Enums\ScoreEventType;
use App\Enums\WinReason;
use App\Events\Game\ConnectorApplied;
use App\Events\Game\DiceRolled;
use App\Events\Game\GameFinished;
use App\Events\Game\MovementBlocked;
use App\Events\Game\PawnMoved;
use App\Events\Game\QuestionPresented;
use App\Events\Game\ScoreUpdated;
use App\Events\Game\SessionPaused;
use App\Events\Game\SessionResumed;
use App\Exceptions\ActiveGameSessionExistsException;
use App\Exceptions\GameAlreadyFinishedException;
use App\Exceptions\NotYourTurnException;
use App\Exceptions\QuestionExpiredException;
use App\Jobs\EvaluateAchievements;
use App\Jobs\EvaluateCertificateEligibility;
use App\Jobs\UpdateLearningProgress;
use App\Models\GameLog;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Models\Soal;
use App\Models\User;
use App\Repositories\Game\GameSettingsRepository;
use App\Services\Game\TurnDecider\HumanTurnDecider;
use App\Services\Game\TurnDecider\RobotTurnDecider;
use App\Services\Game\TurnDecider\TurnDeciderInterface;
use App\Services\Leaderboard\LeaderboardService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Orkestrator satu giliran permainan (Tahap 5/17, keputusan final): setiap
 * aksi signifikan dibungkus SATU DB::transaction(), event domain dikumpulkan
 * selama transaksi lalu HANYA di-dispatch setelah transaksi commit (koreksi
 * kritis Tahap 17) — supaya broadcast tidak pernah terjadi sebelum/jika
 * transaksi rollback.
 */
class GameSessionService
{
    public function __construct(
        private readonly DiceService $dice,
        private readonly MovementService $movement,
        private readonly TileResolverService $tileResolver,
        private readonly QuestionService $question,
        private readonly ScoreService $score,
        private readonly WinConditionService $winCondition,
        private readonly TurnService $turn,
        private readonly GameLogService $gameLog,
        private readonly GameSettingsRepository $settings,
        private readonly PlayerStatsService $playerStats,
        private readonly LeaderboardService $leaderboard,
        private readonly HumanTurnDecider $humanDecider,
        private readonly RobotTurnDecider $robotDecider,
    ) {
    }

    /**
     * Sesi Vs Robot (Tahap 15, keputusan final): dibungkus Cache::lock() untuk
     * mencegah race condition double-klik/multi-tab membuat dua sesi sekaligus.
     */
    public function createVsRobotSession(User $user): GameSession
    {
        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user) {
            $this->assertNoActiveSession($user);

            return DB::transaction(function () use ($user) {
                $papan = PapanPermainan::query()->active()->inRandomOrder()->firstOrFail();

                $gameSession = GameSession::create([
                    'papan_id' => $papan->id,
                    'mode' => GameMode::VsRobot,
                    'status' => GameStatus::Playing,
                    'random_seed' => Str::random(16),
                    'total_turn' => 0,
                    'started_at' => now(),
                ]);

                $human = GamePlayer::create([
                    'game_session_id' => $gameSession->id,
                    'user_id' => $user->id,
                    'is_robot' => false,
                    'turn_order' => 1,
                    'pawn_color' => 'blue',
                    'posisi_pion' => 0,
                    'skor' => 0,
                    'status' => PlayerStatus::Active,
                    'last_heartbeat_at' => now(),
                ]);

                GamePlayer::create([
                    'game_session_id' => $gameSession->id,
                    'user_id' => null,
                    'is_robot' => true,
                    'turn_order' => 2,
                    'pawn_color' => 'red',
                    'posisi_pion' => 0,
                    'skor' => 0,
                    'status' => PlayerStatus::Active,
                ]);

                $gameSession->update(['current_turn_game_player_id' => $human->id]);

                return $gameSession->fresh();
            });
        });
    }

    /**
     * @return array{type: string, session: GameSession, player: GamePlayer, nilai_dadu?: int, effect?: array, soal?: Soal, robot_turns: array}
     */
    public function rollDice(GameSession $gameSession, GamePlayer $gamePlayer): array
    {
        [$result, $events] = DB::transaction(function () use ($gameSession, $gamePlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);
            $this->assertPlayable($gameSession, $gamePlayer);

            $events = [];
            $result = $this->executeRoll($gameSession, $gamePlayer, $events);
            $result['robot_turns'] = $this->playRobotTurnsIfNeeded($gameSession, $events);
            $result['session'] = $gameSession->fresh();
            $result['player'] = $gamePlayer->fresh();

            return [$result, $events];
        });

        $this->dispatchEvents($events);

        return $result;
    }

    /**
     * @return array{type: string, session: GameSession, player: GamePlayer, benar?: bool, robot_turns: array}
     */
    public function submitAnswer(GameSession $gameSession, GamePlayer $gamePlayer, int $soalId, ?string $jawaban): array
    {
        [$result, $events] = DB::transaction(function () use ($gameSession, $gamePlayer, $soalId, $jawaban) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);
            $this->assertPlayable($gameSession, $gamePlayer);

            $events = [];
            $result = $this->executeAnswer($gameSession, $gamePlayer, $soalId, $jawaban, $events);
            $result['robot_turns'] = $this->playRobotTurnsIfNeeded($gameSession, $events);
            $result['session'] = $gameSession->fresh();
            $result['player'] = $gamePlayer->fresh();

            return [$result, $events];
        });

        $this->dispatchEvents($events);

        return $result;
    }

    /**
     * Satu giliran lempar dadu murni (Tahap 10a/17). Diekstrak dari rollDice()
     * agar bisa dipakai ulang baik untuk pemain manusia (via HTTP) maupun
     * robot (dipanggil langsung oleh playRobotTurnsIfNeeded di transaksi yang
     * sama) tanpa duplikasi logic gerak/konektor/efek petak.
     *
     * @return array{type: string, session: GameSession, player: GamePlayer, nilai_dadu: int, soal?: Soal}
     */
    private function executeRoll(GameSession $gameSession, GamePlayer $gamePlayer, array &$events): array
    {
        $papan = $gameSession->papan;

        $nilaiDadu = $this->dice->roll($gameSession);
        $events[] = new DiceRolled($gameSession, $gamePlayer, $nilaiDadu);
        $this->gameLog->log($gameSession, GameLogEventType::DiceRolled, $gamePlayer->user_id, $gameSession->total_turn, [
            'nilai' => $nilaiDadu,
        ]);

        $move = $this->movement->move($gamePlayer, $nilaiDadu, $papan);

        if ($move['blocked']) {
            $events[] = new MovementBlocked($gameSession, $gamePlayer, $nilaiDadu);
            $this->gameLog->log($gameSession, GameLogEventType::MovementBlocked, $gamePlayer->user_id, $gameSession->total_turn, [
                'nilai_dadu' => $nilaiDadu,
            ]);

            $this->turn->advance($gameSession);

            return ['type' => 'blocked', 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu];
        }

        $events[] = new PawnMoved($gameSession, $gamePlayer, $move['posisi_sebelum'], $move['posisi_sesudah']);
        $this->gameLog->log($gameSession, GameLogEventType::PawnMoved, $gamePlayer->user_id, $gameSession->total_turn, [
            'posisi_sebelum' => $move['posisi_sebelum'],
            'posisi_sesudah' => $move['posisi_sesudah'],
        ]);

        if ($move['konektor']) {
            $konektor = $move['konektor'];
            $events[] = new ConnectorApplied($gameSession, $gamePlayer, $konektor->jenis, $konektor->posisi_awal, $konektor->posisi_akhir);
            $this->gameLog->log($gameSession, GameLogEventType::ConnectorApplied, $gamePlayer->user_id, $gameSession->total_turn, [
                'jenis' => $konektor->jenis->value,
                'posisi_awal' => $konektor->posisi_awal,
                'posisi_akhir' => $konektor->posisi_akhir,
            ]);
        }

        if ($this->winCondition->hasWon($gamePlayer, $papan)) {
            $this->finishSession($gameSession, $gamePlayer, WinReason::Finish, $events);

            return ['type' => 'finished', 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu];
        }

        $petak = Petak::query()->where('papan_id', $papan->id)->where('posisi', $move['posisi_sesudah'])->firstOrFail();
        $effect = $this->tileResolver->resolve($gameSession, $gamePlayer, $petak);

        if ($effect['type'] === 'soal') {
            $soal = $this->question->selectQuestion($gameSession, $petak);

            if ($soal) {
                $waktu = $this->settings->getInt('question_timer_seconds');
                $events[] = new QuestionPresented($gameSession, $gamePlayer, $soal, $waktu);
                $this->gameLog->log($gameSession, GameLogEventType::QuestionPresented, $gamePlayer->user_id, $gameSession->total_turn, [
                    'soal_id' => $soal->id,
                ]);

                return ['type' => 'soal', 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu, 'soal' => $soal];
            }

            // Pool soal kategori ini habis dalam sesi ini -> diperlakukan seperti petak biasa.
            $effect = ['type' => 'none'];
        }

        if (in_array($effect['type'], ['bonus', 'penalti', 'mystery'], true)) {
            $scoreEventType = match ($effect['type']) {
                'bonus' => ScoreEventType::Bonus,
                'penalti' => ScoreEventType::TilePenalty,
                'mystery' => $effect['hasil'] === 'bonus' ? ScoreEventType::Bonus : ScoreEventType::TilePenalty,
            };

            $events[] = new ScoreUpdated($gameSession, $gamePlayer, $scoreEventType, $effect['delta'], $gamePlayer->fresh()->skor);
            $this->gameLog->log($gameSession, GameLogEventType::ScoreUpdated, $gamePlayer->user_id, $gameSession->total_turn, [
                'jenis_efek' => $effect['type'],
                'delta' => $effect['delta'],
            ]);
        }

        $this->turn->advance($gameSession);

        return ['type' => $effect['type'], 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu];
    }

    /**
     * Satu jawaban soal (Tahap 10a/17), diekstrak agar dipakai ulang oleh
     * submitAnswer() (pemain manusia lewat HTTP) maupun RobotTurnDecider
     * (dipanggil langsung oleh playRobotTurnsIfNeeded pada giliran robot).
     *
     * @return array{type: string, session: GameSession, player: GamePlayer, benar: bool, pembahasan: string, kunci_jawaban: string}
     */
    private function executeAnswer(GameSession $gameSession, GamePlayer $gamePlayer, int $soalId, ?string $jawaban, array &$events): array
    {
        if ($gameSession->active_question_id !== $soalId) {
            throw new QuestionExpiredException();
        }

        $sudahKedaluwarsa = $gameSession->active_question_expires_at !== null
            && $gameSession->active_question_expires_at->isPast();

        $soal = Soal::query()->withTrashed()->findOrFail($soalId);
        $isCorrect = ! $sudahKedaluwarsa && $jawaban !== null
            && strtoupper($jawaban) === strtoupper($soal->kunci_jawaban);

        $scoreEventType = $isCorrect ? ScoreEventType::CorrectAnswer : ScoreEventType::WrongAnswer;
        $delta = $this->score->apply($gamePlayer, $scoreEventType);

        $this->gameLog->log($gameSession, GameLogEventType::AnswerSubmitted, $gamePlayer->user_id, $gameSession->total_turn, [
            'soal_id' => $soal->id,
            'kategori_id' => $soal->kategori_id,
            'is_correct' => $isCorrect,
            'game_player_id' => $gamePlayer->id,
        ]);

        $events[] = new ScoreUpdated($gameSession, $gamePlayer, $scoreEventType, $delta, $gamePlayer->fresh()->skor);

        // Tahap 13a: progress belajar hanya berlaku untuk pemain MANUSIA (robot
        // tidak punya user_id). ->afterCommit() aman dipanggil dari sini walau
        // executeAnswer() dieksekusi di dalam transaksi rollDice()/submitAnswer()
        // manapun yang memanggilnya - job baru benar-benar di-push setelah
        // transaksi terluar commit.
        if ($gamePlayer->user_id !== null) {
            UpdateLearningProgress::dispatch($gamePlayer->user, $soal->kategori_id, $isCorrect)->afterCommit();
        }

        $gameSession->update(['active_question_id' => null, 'active_question_expires_at' => null]);

        $this->turn->advance($gameSession);

        return [
            'type' => 'answered',
            'session' => $gameSession->fresh(),
            'player' => $gamePlayer->fresh(),
            'benar' => $isCorrect,
            'pembahasan' => $soal->pembahasan,
            'kunci_jawaban' => $soal->kunci_jawaban,
        ];
    }

    /**
     * Robot AI (Tahap 11, keputusan final): dijalankan SINKRON dalam transaksi
     * yang sama dengan giliran manusia yang memicunya (bukan queue/job
     * terjadwal) — begitu giliran berpindah ke robot, orkestrator langsung
     * memainkan giliran robot itu sendiri (dadu, gerak, dan jika kena petak
     * soal, jawaban lewat RobotTurnDecider) sampai giliran kembali ke manusia
     * atau permainan selesai. Hasil tiap giliran robot dikumpulkan agar
     * frontend bisa menampilkan urutan kejadian, bukan hanya state akhir.
     *
     * @return array<int, array{nilai_dadu: ?int, type: string, benar?: bool}>
     */
    private function playRobotTurnsIfNeeded(GameSession $gameSession, array &$events): array
    {
        $robotTurns = [];

        while ($gameSession->status === GameStatus::Playing) {
            $current = GamePlayer::query()->find($gameSession->current_turn_game_player_id);

            if (! $current || ! $this->deciderFor($current)->shouldAutoPlay($current)) {
                break;
            }

            $rollResult = $this->executeRoll($gameSession, $current, $events);
            $turnSummary = ['nilai_dadu' => $rollResult['nilai_dadu'], 'type' => $rollResult['type']];

            if ($rollResult['type'] === 'soal' && isset($rollResult['soal'])) {
                $jawaban = $this->deciderFor($current)->decideAnswer($gameSession, $current, $rollResult['soal']);
                $answerResult = $this->executeAnswer($gameSession, $current, $rollResult['soal']->id, $jawaban, $events);
                $turnSummary['benar'] = $answerResult['benar'];
            }

            $robotTurns[] = $turnSummary;
        }

        return $robotTurns;
    }

    private function deciderFor(GamePlayer $gamePlayer): TurnDeciderInterface
    {
        return $gamePlayer->is_robot ? $this->robotDecider : $this->humanDecider;
    }

    /**
     * Vs-Robot tidak mengenal status Paused/Forfeit (Tahap 18, keputusan final)
     * — keluar dari permainan berarti sesi ditandai Abandoned tanpa pemenang.
     * Multiplayer (Tahap 12a): keluar secara sukarela langsung menyatakan lawan
     * menang WO (`WinReason::Forfeit`) — beda dari disconnect tak sengaja, yang
     * masih diberi masa tenggang 60 detik lewat pauseForDisconnect().
     */
    public function leave(GameSession $gameSession, GamePlayer $gamePlayer): void
    {
        $events = DB::transaction(function () use ($gameSession, $gamePlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if (in_array($gameSession->status, [GameStatus::Finished, GameStatus::Abandoned], true)) {
                throw new GameAlreadyFinishedException();
            }

            $events = [];
            $this->gameLog->log($gameSession, GameLogEventType::Forfeited, $gamePlayer->user_id, $gameSession->total_turn, [
                'reason' => 'left_voluntarily',
            ]);

            if ($gameSession->mode === GameMode::Multiplayer) {
                $opponent = $gameSession->players()->where('id', '!=', $gamePlayer->id)->firstOrFail();
                $this->finishSession($gameSession, $opponent, WinReason::Forfeit, $events);
            } else {
                $gameSession->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);
            }

            if ($gamePlayer->user_id !== null) {
                Cache::forget($this->playerStats->cacheKey($gamePlayer->user));
            }

            return $events;
        });

        $this->dispatchEvents($events);
    }

    /**
     * Sinyal "aku masih di sini" dari klien multiplayer (Tahap 12a). Jika
     * pemain sebelumnya ditandai Disconnected & sesi Paused, koneksi yang
     * kembali di sini langsung memulihkan sesi tanpa menunggu sapuan
     * `game:check-heartbeats` berikutnya — jalur cepat untuk reconnect.
     */
    public function heartbeat(GameSession $gameSession, GamePlayer $gamePlayer): void
    {
        $events = DB::transaction(function () use ($gameSession, $gamePlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if (in_array($gameSession->status, [GameStatus::Finished, GameStatus::Abandoned], true)) {
                return [];
            }

            $events = [];
            $gamePlayer->update(['last_heartbeat_at' => now()]);

            if ($gamePlayer->status === PlayerStatus::Disconnected && $gameSession->status === GameStatus::Paused) {
                $gamePlayer->update(['status' => PlayerStatus::Active]);
                $gameSession->update(['status' => GameStatus::Playing]);
                $this->gameLog->log($gameSession, GameLogEventType::Resumed, $gamePlayer->user_id, $gameSession->total_turn, []);
                $events[] = new SessionResumed($gameSession, $gamePlayer);
            }

            return $events;
        });

        $this->dispatchEvents($events);
    }

    /**
     * Dipanggil command terjadwal `game:check-heartbeats` (Tahap 12a) saat
     * heartbeat seorang pemain multiplayer terdeteksi basi — sesi dijeda
     * (Paused), BUKAN langsung forfeit, sesuai keputusan "60 detik masa
     * tenggang reconnect" (Stage 1).
     */
    public function pauseForDisconnect(GameSession $gameSession, GamePlayer $disconnectedPlayer): void
    {
        $events = DB::transaction(function () use ($gameSession, $disconnectedPlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if ($gameSession->status !== GameStatus::Playing) {
                return [];
            }

            $events = [];
            $disconnectedPlayer->update(['status' => PlayerStatus::Disconnected]);
            $gameSession->update(['status' => GameStatus::Paused]);
            $this->gameLog->log($gameSession, GameLogEventType::Paused, $disconnectedPlayer->user_id, $gameSession->total_turn, [
                'reason' => 'heartbeat_timeout',
            ]);
            $events[] = new SessionPaused($gameSession, $disconnectedPlayer);

            return $events;
        });

        $this->dispatchEvents($events);
    }

    /**
     * Dipanggil command terjadwal saat pemain yang di-pause ternyata sudah
     * heartbeat lagi (sebelum sapuan berikutnya sempat memanggil heartbeat()
     * sendiri) — jalur "masih sempat" di luar jalur cepat heartbeat().
     */
    public function resumeFromDisconnect(GameSession $gameSession, GamePlayer $reconnectedPlayer): void
    {
        $events = DB::transaction(function () use ($gameSession, $reconnectedPlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if ($gameSession->status !== GameStatus::Paused) {
                return [];
            }

            $events = [];
            $reconnectedPlayer->update(['status' => PlayerStatus::Active]);
            $gameSession->update(['status' => GameStatus::Playing]);
            $this->gameLog->log($gameSession, GameLogEventType::Resumed, $reconnectedPlayer->user_id, $gameSession->total_turn, []);
            $events[] = new SessionResumed($gameSession, $reconnectedPlayer);

            return $events;
        });

        $this->dispatchEvents($events);
    }

    /**
     * Dipanggil command terjadwal saat pemain yang di-pause karena disconnect
     * TIDAK reconnect dalam `reconnect_timeout_seconds` — lawan menang WO
     * (Tahap 1, keputusan final: disconnect timeout -> Forfeit).
     */
    public function forfeitDueToDisconnect(GameSession $gameSession, GamePlayer $disconnectedPlayer): void
    {
        $events = DB::transaction(function () use ($gameSession, $disconnectedPlayer) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if ($gameSession->status !== GameStatus::Paused) {
                return [];
            }

            $events = [];
            $opponent = $gameSession->players()->where('id', '!=', $disconnectedPlayer->id)->firstOrFail();

            $this->gameLog->log($gameSession, GameLogEventType::Forfeited, $disconnectedPlayer->user_id, $gameSession->total_turn, [
                'reason' => 'disconnect_timeout',
            ]);
            $this->finishSession($gameSession, $opponent, WinReason::Forfeit, $events);

            return $events;
        });

        $this->dispatchEvents($events);
    }

    private function finishSession(GameSession $gameSession, GamePlayer $pemenang, WinReason $winReason, array &$events): void
    {
        $winDelta = $this->score->apply($pemenang, ScoreEventType::Win);
        $events[] = new ScoreUpdated($gameSession, $pemenang, ScoreEventType::Win, $winDelta, $pemenang->fresh()->skor);

        foreach ($gameSession->players as $player) {
            $player->update(['accuracy' => $this->computeAccuracy($gameSession, $player)]);
        }

        $gameSession->update([
            'status' => GameStatus::Finished,
            'winner_game_player_id' => $pemenang->id,
            'win_reason' => $winReason,
            'finished_at' => now(),
            'duration_seconds' => $gameSession->started_at ? now()->diffInSeconds($gameSession->started_at) : null,
        ]);

        $this->gameLog->log($gameSession, GameLogEventType::Finished, null, $gameSession->total_turn, [
            'winner_game_player_id' => $pemenang->id,
        ]);

        $events[] = new GameFinished($gameSession, $pemenang, $winReason);

        // Tahap 13c: leaderboard (global + per-mode) berubah setiap sesi selesai.
        $this->leaderboard->forgetAll();

        foreach ($gameSession->players as $player) {
            if ($player->user_id !== null) {
                Cache::forget($this->playerStats->cacheKey($player->user));
                // Tahap 13a: evaluasi achievement HARUS jalan untuk menang MAUPUN
                // kalah (total_permainan tetap bertambah) — bukan hanya pemenang.
                EvaluateAchievements::dispatch($player->user)->afterCommit();
                // Tahap 13b: sertifikat juga dievaluasi setiap sesi selesai (menang
                // atau kalah) — kelayakannya murni dari progres kumulatif pemain,
                // bukan dari hasil sesi ini.
                EvaluateCertificateEligibility::dispatch($player->user)->afterCommit();
            }
        }
    }

    private function computeAccuracy(GameSession $gameSession, GamePlayer $player): ?float
    {
        $logs = GameLog::query()
            ->where('game_session_id', $gameSession->id)
            ->where('event_type', GameLogEventType::AnswerSubmitted->value)
            ->get()
            ->filter(fn (GameLog $log) => ($log->payload['game_player_id'] ?? null) === $player->id);

        if ($logs->isEmpty()) {
            return null;
        }

        $benar = $logs->filter(fn (GameLog $log) => $log->payload['is_correct'] ?? false)->count();

        return round($benar / $logs->count() * 100, 2);
    }

    /**
     * Dipakai baik oleh Vs Robot (langsung Playing) maupun MatchmakingService
     * Tahap 12a (bisa Waiting selagi mengantre/menunggu lawan mengisi room) —
     * mencegah user membuat/mengantre sesi kedua selagi salah satu masih ada.
     */
    public function assertNoActiveSession(User $user): void
    {
        $adaSesiAktif = GamePlayer::query()
            ->where('user_id', $user->id)
            ->whereHas('gameSession', fn ($query) => $query->whereIn('status', [
                GameStatus::Waiting->value,
                GameStatus::Playing->value,
                GameStatus::Paused->value,
            ]))
            ->exists();

        if ($adaSesiAktif) {
            throw new ActiveGameSessionExistsException();
        }
    }

    private function assertPlayable(GameSession $gameSession, GamePlayer $gamePlayer): void
    {
        if ($gameSession->status !== GameStatus::Playing) {
            throw new GameAlreadyFinishedException();
        }

        if ($gameSession->current_turn_game_player_id !== $gamePlayer->id) {
            throw new NotYourTurnException();
        }
    }

    private function dispatchEvents(array $events): void
    {
        foreach ($events as $domainEvent) {
            event($domainEvent);
        }
    }
}
