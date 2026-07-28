<?php

namespace App\Services\Game;

use App\Enums\GameLogEventType;
use App\Enums\GameMode;
use App\Enums\GameStatus;
use App\Enums\PawnColor;
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
use App\Services\Achievement\AchievementEvaluationService;
use App\Services\Game\TurnDecider\HumanTurnDecider;
use App\Services\Game\TurnDecider\RobotTurnDecider;
use App\Services\Game\TurnDecider\TurnDeciderInterface;
use App\Services\Leaderboard\LeaderboardService;
use App\Services\Notification\NotificationService;
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
        private readonly DuelService $duel,
        private readonly GameLogService $gameLog,
        private readonly GameSettingsRepository $settings,
        private readonly PlayerStatsService $playerStats,
        private readonly LeaderboardService $leaderboard,
        private readonly HumanTurnDecider $humanDecider,
        private readonly RobotTurnDecider $robotDecider,
        private readonly AchievementEvaluationService $achievementEvaluation,
        private readonly NotificationService $notifications,
    ) {
    }

    /**
     * Notifikasi (database + broadcast) yang dikumpulkan selama satu transaksi
     * lalu baru benar-benar dikirim lewat flushNotifications() SETELAH commit
     * -- disiplin yang sama dengan $events/dispatchEvents() (Tahap 17): broadcast
     * tidak boleh terjadi sebelum/kalau transaksinya batal.
     *
     * @var array<int, array{0: User, 1: array}>
     */
    private array $pendingNotifications = [];

    private function queueNotification(User $user, array $payload): void
    {
        $this->pendingNotifications[] = [$user, $payload];
    }

    private function flushNotifications(): void
    {
        foreach ($this->pendingNotifications as [$user, $payload]) {
            $this->notifications->send($user, $payload);
        }

        $this->pendingNotifications = [];
    }

    /**
     * Sesi Vs Robot (Tahap 15, keputusan final): dibungkus Cache::lock() untuk
     * mencegah race condition double-klik/multi-tab membuat dua sesi sekaligus.
     */
    public function createVsRobotSession(User $user, ?string $pawnColor = null): GameSession
    {
        return Cache::lock("create-session-user-{$user->id}", 10)->block(5, function () use ($user, $pawnColor) {
            $this->assertNoActiveSession($user);

            return DB::transaction(function () use ($user, $pawnColor) {
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
                    'pawn_color' => $pawnColor ?? PawnColor::Biru->value,
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
    public function rollDice(GameSession $gameSession, GamePlayer $gamePlayer, ?int $forcedRoll = null): array
    {
        [$result, $events] = DB::transaction(function () use ($gameSession, $gamePlayer, $forcedRoll) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);
            $this->assertPlayable($gameSession, $gamePlayer);

            $events = [];
            $result = $this->executeRoll($gameSession, $gamePlayer, $events, $forcedRoll);
            $robotOutcome = $this->playRobotTurnsIfNeeded($gameSession, $events);
            $result['robot_turns'] = $robotOutcome['robot_turns'];
            $result['_achievements_by_player'] = array_replace($result['_achievements_by_player'] ?? [], $robotOutcome['achievements_by_player']);
            $result['session'] = $gameSession->fresh();
            $result['player'] = $gamePlayer->fresh();

            return [$result, $events];
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
        $this->extractNewlyUnlockedAchievements($result, $gamePlayer);

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
            $robotOutcome = $this->playRobotTurnsIfNeeded($gameSession, $events);
            $result['robot_turns'] = $robotOutcome['robot_turns'];
            $result['_achievements_by_player'] = array_replace($result['_achievements_by_player'] ?? [], $robotOutcome['achievements_by_player']);
            $result['session'] = $gameSession->fresh();
            $result['player'] = $gamePlayer->fresh();

            return [$result, $events];
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
        $this->extractNewlyUnlockedAchievements($result, $gamePlayer);

        return $result;
    }

    public function submitDuelAnswer(GameSession $gameSession, GamePlayer $gamePlayer, int $soalId, ?string $jawaban, int $timeTakenMs): array
    {
        [$result, $events] = DB::transaction(function () use ($gameSession, $gamePlayer, $soalId, $jawaban, $timeTakenMs) {
            $gameSession = GameSession::query()->lockForUpdate()->findOrFail($gameSession->id);

            if ($gameSession->status !== GameStatus::Duel) {
                abort(403, 'Permainan tidak sedang dalam mode duel.');
            }

            $events = [];
            $result = $this->duel->submitDuelAnswer($gameSession, $gamePlayer, $soalId, $jawaban, $timeTakenMs);

            if ($result['type'] === 'duel_finished') {
                $robotOutcome = $this->playRobotTurnsIfNeeded($gameSession, $events);
                $result['robot_turns'] = $robotOutcome['robot_turns'];
                $result['_achievements_by_player'] = $robotOutcome['achievements_by_player'] ?? [];
            }

            $result['session'] = $gameSession->fresh();
            $result['player'] = $gamePlayer->fresh();

            return [$result, $events];
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
        $this->extractNewlyUnlockedAchievements($result, $gamePlayer);

        return $result;
    }

    /**
     * Achievement baru yang terbuka untuk SATU game_player tertentu dievaluasi
     * synchronous di dalam finishSession() (bukan hanya lewat job terjadwal —
     * lihat catatan di finishSession()) supaya bisa langsung disisipkan ke
     * respons roll/answer yang memicu akhir permainan, untuk popup seketika di
     * frontend alih-alih baru terlihat saat membuka halaman Achievement lain
     * kali. Dipanggil di rollDice()/submitAnswer() setelah transaksi commit.
     */
    private function extractNewlyUnlockedAchievements(array &$result, GamePlayer $gamePlayer): void
    {
        $achievementsByPlayer = $result['_achievements_by_player'] ?? [];
        unset($result['_achievements_by_player']);

        $result['newly_unlocked_achievements'] = $achievementsByPlayer[$gamePlayer->id] ?? [];
    }

    /**
     * Satu giliran lempar dadu murni (Tahap 10a/17). Diekstrak dari rollDice()
     * agar bisa dipakai ulang baik untuk pemain manusia (via HTTP) maupun
     * robot (dipanggil langsung oleh playRobotTurnsIfNeeded di transaksi yang
     * sama) tanpa duplikasi logic gerak/konektor/efek petak.
     *
     * @return array{type: string, session: GameSession, player: GamePlayer, nilai_dadu: int, soal?: Soal}
     */
    private function executeRoll(GameSession $gameSession, GamePlayer $gamePlayer, array &$events, ?int $forcedRoll = null): array
    {
        $papan = $gameSession->papan;

        if (app()->environment('local') && $forcedRoll !== null && $forcedRoll >= 1 && $forcedRoll <= 100) {
            $nilaiDadu = $forcedRoll;
        } else {
            $nilaiDadu = $this->dice->roll($gameSession);
        }
        
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

        if ($this->winCondition->hasWon($gamePlayer, $papan)) {
            $achievementsByPlayer = $this->finishSession($gameSession, $gamePlayer, WinReason::Finish, $events);

            return [
                'type' => 'finished',
                'session' => $gameSession->fresh(),
                'player' => $gamePlayer->fresh(),
                'nilai_dadu' => $nilaiDadu,
                '_achievements_by_player' => $achievementsByPlayer,
            ];
        }

        $petak = Petak::query()->where('papan_id', $papan->id)->where('posisi', $move['posisi_sesudah'])->firstOrFail();

        if ($move['konektor']) {
            $konektor = $move['konektor'];
            $soal = $this->question->selectQuestion($gameSession, $petak);

            if ($soal) {
                $waktu = $this->settings->getInt('question_timer_seconds');
                $gameSession->update([
                    'active_question_id' => $soal->id,
                    'active_question_expires_at' => now()->addSeconds($waktu),
                ]);

                $events[] = new QuestionPresented($gameSession, $gamePlayer, $soal, $waktu);
                $this->gameLog->log($gameSession, GameLogEventType::QuestionPresented, $gamePlayer->user_id, $gameSession->total_turn, [
                    'soal_id' => $soal->id,
                ]);

                return ['type' => 'soal', 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu, 'soal' => $soal];
            } else {
                // Jika tidak ada soal tersisa, langsung terapkan konektor
                $events[] = new ConnectorApplied($gameSession, $gamePlayer, $konektor->jenis, $konektor->posisi_awal, $konektor->posisi_akhir);
                $this->gameLog->log($gameSession, GameLogEventType::ConnectorApplied, $gamePlayer->user_id, $gameSession->total_turn, [
                    'jenis' => $konektor->jenis->value,
                    'posisi_awal' => $konektor->posisi_awal,
                    'posisi_akhir' => $konektor->posisi_akhir,
                ]);
                $gamePlayer->update(['posisi_pion' => $konektor->posisi_akhir]);
            }
        }

        $effect = $this->tileResolver->resolve($gameSession, $gamePlayer, $petak);

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

        $activeDuel = $this->duel->checkAndStartDuel($gameSession, $gamePlayer, $events);
        if ($activeDuel) {
            return ['type' => 'duel', 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu, 'duel' => $activeDuel];
        }

        $this->turn->advance($gameSession);

        $response = ['type' => $effect['type'] === 'none' ? 'normal' : $effect['type'], 'session' => $gameSession->fresh(), 'player' => $gamePlayer->fresh(), 'nilai_dadu' => $nilaiDadu];
        
        return $response;
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

        $konektor_applied = false;
        $konektor = \App\Models\PapanKonektor::query()
            ->where('papan_id', $gameSession->papan_id)
            ->where('posisi_awal', $gamePlayer->posisi_pion)
            ->first();

        if ($konektor) {
            $jenis = $konektor->jenis;
            if (($isCorrect && $jenis->value === 'tangga') || (!$isCorrect && $jenis->value === 'ular')) {
                $events[] = new ConnectorApplied($gameSession, $gamePlayer, $jenis, $konektor->posisi_awal, $konektor->posisi_akhir);
                $this->gameLog->log($gameSession, GameLogEventType::ConnectorApplied, $gamePlayer->user_id, $gameSession->total_turn, [
                    'jenis' => $jenis->value,
                    'posisi_awal' => $konektor->posisi_awal,
                    'posisi_akhir' => $konektor->posisi_akhir,
                ]);
                $gamePlayer->update(['posisi_pion' => $konektor->posisi_akhir]);
                $konektor_applied = true;
            }
        }

        $activeDuel = $this->duel->checkAndStartDuel($gameSession, $gamePlayer, $events);

        if (!$activeDuel) {
            $this->turn->advance($gameSession);
        }

        return [
            'type' => $activeDuel ? 'duel' : 'answered',
            'duel' => $activeDuel,
            'session' => $gameSession->fresh(),
            'player' => $gamePlayer->fresh(),
            'benar' => $isCorrect,
            'pembahasan' => $soal->pembahasan,
            'kunci_jawaban' => $soal->kunci_jawaban,
            'konektor_applied' => $konektor_applied,
            'konektor_info' => $konektor ? [
                'jenis' => $konektor->jenis->value,
                'posisi_awal' => $konektor->posisi_awal,
                'posisi_akhir' => $konektor->posisi_akhir,
            ] : null,
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
    /**
     * @return array{robot_turns: array, achievements_by_player: array}
     */
    private function playRobotTurnsIfNeeded(GameSession $gameSession, array &$events): array
    {
        $robotTurns = [];
        $achievementsByPlayer = [];

        while ($gameSession->status === GameStatus::Playing) {
            $current = GamePlayer::query()->find($gameSession->current_turn_game_player_id);

            if (! $current || ! $this->deciderFor($current)->shouldAutoPlay($current)) {
                break;
            }

            $rollResult = $this->executeRoll($gameSession, $current, $events);
            $turnSummary = ['nilai_dadu' => $rollResult['nilai_dadu'], 'type' => $rollResult['type']];
            // Giliran robot bisa juga yang menyelesaikan sesi (robot mencapai
            // Finish) — kalau begitu, semua pemain (termasuk manusia) sudah
            // dievaluasi achievement-nya di finishSession(); teruskan supaya
            // rollDice()/submitAnswer() bisa menyertakannya di respons pemain.
            $achievementsByPlayer = array_replace($achievementsByPlayer, $rollResult['_achievements_by_player'] ?? []);

            if ($rollResult['type'] === 'soal' && isset($rollResult['soal'])) {
                $jawaban = $this->deciderFor($current)->decideAnswer($gameSession, $current, $rollResult['soal']);
                $answerResult = $this->executeAnswer($gameSession, $current, $rollResult['soal']->id, $jawaban, $events);
                $turnSummary['benar'] = $answerResult['benar'];
                $turnSummary['kunci_jawaban'] = $answerResult['kunci_jawaban'] ?? null;
                $turnSummary['jawaban_robot'] = $jawaban;
                $turnSummary['soal'] = [
                    'pertanyaan' => $rollResult['soal']->pertanyaan,
                    'opsi_jawaban' => $rollResult['soal']->opsi_jawaban,
                ];
                $turnSummary['konektor_applied'] = $answerResult['konektor_applied'] ?? false;
                $turnSummary['konektor_info'] = $answerResult['konektor_info'] ?? null;
                $achievementsByPlayer = array_replace($achievementsByPlayer, $answerResult['_achievements_by_player'] ?? []);
            }

            $robotTurns[] = $turnSummary;
        }

        return ['robot_turns' => $robotTurns, 'achievements_by_player' => $achievementsByPlayer];
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
                $giliranDia = $gameSession->current_turn_game_player_id === $gamePlayer->id;
                $gamePlayer->update(['status' => PlayerStatus::Forfeited]);

                $aktifLainnya = $this->activePlayersExcept($gameSession, $gamePlayer->id);

                if ($aktifLainnya->count() <= 1) {
                    $pemenang = $aktifLainnya->first() ?? $gameSession->players()->where('id', '!=', $gamePlayer->id)->firstOrFail();
                    $this->finishSession($gameSession, $pemenang, WinReason::Forfeit, $events);
                } elseif ($giliranDia) {
                    // 2+ pemain lain masih aktif - sesi lanjut tanpa dia (keputusan final Tahap N-pemain), cuma majukan giliran kalau kebetulan gilirannya dia.
                    $this->turn->advance($gameSession);
                }
            } else {
                $gameSession->update(['status' => GameStatus::Abandoned, 'finished_at' => now()]);
            }

            if ($gamePlayer->user_id !== null) {
                Cache::forget($this->playerStats->cacheKey($gamePlayer->user));
            }

            return $events;
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
    }

    /** Pemain berstatus Active di sesi ini, tidak termasuk $excludeId — dipakai leave()/pauseForDisconnect()/forfeitDueToDisconnect() untuk generalisasi N pemain. */
    private function activePlayersExcept(GameSession $gameSession, int $excludeId)
    {
        return $gameSession->players()
            ->where('id', '!=', $excludeId)
            ->where('status', PlayerStatus::Active)
            ->get();
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

            if ($gamePlayer->status === PlayerStatus::Disconnected) {
                $gamePlayer->update(['status' => PlayerStatus::Active]);

                // Sesi cuma perlu di-resume kalau memang sempat di-pause total
                // (kasus <2 pemain aktif) - untuk game 3+ pemain yang sesinya
                // TETAP Playing selama dia terputus, tidak ada yang perlu diubah
                // di level sesi, cukup status pemainnya kembali Active.
                if ($gameSession->status === GameStatus::Paused) {
                    $gameSession->update(['status' => GameStatus::Playing]);
                    $this->gameLog->log($gameSession, GameLogEventType::Resumed, $gamePlayer->user_id, $gameSession->total_turn, []);
                    $events[] = new SessionResumed($gameSession, $gamePlayer);
                }
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
            $giliranDia = $gameSession->current_turn_game_player_id === $disconnectedPlayer->id;
            $disconnectedPlayer->update(['status' => PlayerStatus::Disconnected]);
            $this->gameLog->log($gameSession, GameLogEventType::Paused, $disconnectedPlayer->user_id, $gameSession->total_turn, [
                'reason' => 'heartbeat_timeout',
            ]);

            $aktifLainnya = $this->activePlayersExcept($gameSession, $disconnectedPlayer->id);

            if ($aktifLainnya->count() >= 2) {
                // 2+ pemain lain masih aktif - sesi TETAP Playing (bukan pause
                // total), cukup lewati giliran pemain yang terputus (keputusan
                // final: "lanjut tanpa dia" untuk game 3-6 pemain).
                if ($giliranDia) {
                    $this->turn->advance($gameSession);
                }
            } else {
                // <2 pemain aktif tersisa - game tidak bisa lanjut, pause total (perilaku 2-pemain yang sudah ada).
                $gameSession->update(['status' => GameStatus::Paused]);
                $events[] = new SessionPaused($gameSession, $disconnectedPlayer);
            }

            foreach ($aktifLainnya as $lain) {
                if ($lain->user_id !== null) {
                    $this->queueNotification($lain->user, $this->notifications->payloadOpponentDisconnected($disconnectedPlayer));
                }
            }

            return $events;
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
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

            if (! in_array($gameSession->status, [GameStatus::Playing, GameStatus::Paused], true)) {
                return [];
            }

            $events = [];
            $wasPaused = $gameSession->status === GameStatus::Paused;
            $reconnectedPlayer->update(['status' => PlayerStatus::Active]);

            if ($wasPaused) {
                $gameSession->update(['status' => GameStatus::Playing]);
                $this->gameLog->log($gameSession, GameLogEventType::Resumed, $reconnectedPlayer->user_id, $gameSession->total_turn, []);
                $events[] = new SessionResumed($gameSession, $reconnectedPlayer);
            }

            foreach ($this->activePlayersExcept($gameSession, $reconnectedPlayer->id) as $lain) {
                if ($lain->user_id !== null) {
                    $this->queueNotification($lain->user, $this->notifications->payloadOpponentReconnected($reconnectedPlayer));
                }
            }

            return $events;
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
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

            if (! in_array($gameSession->status, [GameStatus::Playing, GameStatus::Paused], true)) {
                return [];
            }

            $events = [];
            $giliranDia = $gameSession->current_turn_game_player_id === $disconnectedPlayer->id;
            $disconnectedPlayer->update(['status' => PlayerStatus::Forfeited]);

            $this->gameLog->log($gameSession, GameLogEventType::Forfeited, $disconnectedPlayer->user_id, $gameSession->total_turn, [
                'reason' => 'disconnect_timeout',
            ]);

            $aktifLainnya = $this->activePlayersExcept($gameSession, $disconnectedPlayer->id);

            if ($aktifLainnya->count() <= 1) {
                $pemenang = $aktifLainnya->first() ?? $gameSession->players()->where('id', '!=', $disconnectedPlayer->id)->firstOrFail();
                $this->finishSession($gameSession, $pemenang, WinReason::Forfeit, $events);
            } else {
                // 2+ pemain lain masih aktif - sesi lanjut normal, cukup pastikan
                // status Playing (kalau kebetulan sempat Paused) & lewati giliran
                // pemain yang baru di-forfeit kalau kebetulan gilirannya dia.
                if ($gameSession->status === GameStatus::Paused) {
                    $gameSession->update(['status' => GameStatus::Playing]);
                }
                if ($giliranDia) {
                    $this->turn->advance($gameSession);
                }
            }

            return $events;
        });

        $this->dispatchEvents($events);
        $this->flushNotifications();
    }

    /**
     * @return array<int, array<int, array{kode: string, nama: string, deskripsi: ?string, icon: ?string, warna_badge: ?string, reward_poin: int}>>
     *         achievement yang baru diraih pada pemanggilan ini, dikelompokkan per game_player_id.
     */
    private function finishSession(GameSession $gameSession, GamePlayer $pemenang, WinReason $winReason, array &$events): array
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
            // abs()+round(): Carbon 3 defaults diffInSeconds() to a SIGNED, possibly
            // fractional result (breaking change vs Carbon 2's always-positive
            // integer) — started_at is always in the past here, but relying on
            // that sign convention crashes the unsignedInteger column the moment
            // it doesn't hold, so normalize explicitly instead.
            'duration_seconds' => $gameSession->started_at ? (int) round(abs(now()->diffInSeconds($gameSession->started_at))) : null,
        ]);

        $this->gameLog->log($gameSession, GameLogEventType::Finished, null, $gameSession->total_turn, [
            'winner_game_player_id' => $pemenang->id,
        ]);

        $events[] = new GameFinished($gameSession, $pemenang, $winReason);

        // Tahap 13c: leaderboard (global + per-mode) berubah setiap sesi selesai.
        $this->leaderboard->forgetAll();

        $achievementsByPlayer = [];

        foreach ($gameSession->players as $player) {
            if ($player->user_id !== null) {
                Cache::forget($this->playerStats->cacheKey($player->user));

                // Evaluasi achievement dijalankan SYNCHRONOUS di sini (bukan hanya
                // lewat job terjadwal di bawah) supaya request roll/answer yang
                // memicu akhir permainan bisa langsung tahu achievement apa yang
                // baru terbuka dan menyertakannya di respons (untuk popup seketika
                // di frontend — lihat rollDice()/submitAnswer()). evaluate() HARUS
                // jalan untuk menang MAUPUN kalah (total_permainan tetap bertambah).
                $granted = $this->achievementEvaluation->evaluate($player->user);
                $achievementsByPlayer[$player->id] = $granted->map(fn ($achievement) => [
                    'kode' => $achievement->kode,
                    'nama' => $achievement->nama,
                    'deskripsi' => $achievement->deskripsi,
                    'icon' => $achievement->icon,
                    'warna_badge' => $achievement->warna_badge,
                    'reward_poin' => $achievement->reward_poin,
                ])->values()->all();

                // Notifikasi (Bell Notification, terintegrasi Tahap 19): permainan
                // selesai (menang/kalah) + achievement baru + rekor skor pribadi baru,
                // untuk pemain manusia yang bersangkutan dan fan-out ke seluruh admin.
                // Dikumpulkan lewat queueNotification() -> dikirim setelah commit oleh
                // flushNotifications() di rollDice()/submitAnswer()/leave()/dst.
                $won = $player->id === $pemenang->id;
                $this->queueNotification($player->user, $this->notifications->payloadGameFinished($player, $gameSession, $won));

                foreach ($achievementsByPlayer[$player->id] as $achievement) {
                    $this->queueNotification($player->user, $this->notifications->payloadAchievementUnlocked($achievement));
                    foreach ($this->notifications->adminUsers() as $admin) {
                        $this->queueNotification($admin, $this->notifications->payloadAchievementUnlockedAdmin($player->user, $achievement));
                    }
                }

                $previousBest = GamePlayer::query()
                    ->where('user_id', $player->user_id)
                    ->where('id', '!=', $player->id)
                    ->whereHas('gameSession', fn ($query) => $query
                        ->where('mode', $gameSession->mode)
                        ->where('status', GameStatus::Finished))
                    ->max('skor');

                if ($previousBest !== null && $player->skor > $previousBest) {
                    $this->queueNotification($player->user, $this->notifications->payloadPersonalBest($gameSession, $player->skor));
                    foreach ($this->notifications->adminUsers() as $admin) {
                        $this->queueNotification($admin, $this->notifications->payloadPersonalBestAdmin($player->user, $player->skor));
                    }
                }

                // Dipertahankan sebagai jaring pengaman eventual-consistency (mis.
                // andai evaluate() di atas gagal karena alasan tak terduga) — sudah
                // idempoten, jadi aman dipanggil lagi walau achievement yang sama
                // sudah diberikan barusan (whereNotIn achievement_id yang sudah ada).
                EvaluateAchievements::dispatch($player->user)->afterCommit();
                // Tahap 13b: sertifikat juga dievaluasi setiap sesi selesai (menang
                // atau kalah) — kelayakannya murni dari progres kumulatif pemain,
                // bukan dari hasil sesi ini.
                EvaluateCertificateEligibility::dispatch($player->user)->afterCommit();
            }
        }

        // Satu notifikasi admin per sesi selesai (bukan per pemain) supaya feed
        // admin tidak duplikat pada mode Multiplayer (2 pemain manusia).
        foreach ($this->notifications->adminUsers() as $admin) {
            $this->queueNotification($admin, $this->notifications->payloadGameFinishedAdmin($pemenang, $gameSession));
        }

        return $achievementsByPlayer;
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
