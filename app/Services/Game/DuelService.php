<?php

namespace App\Services\Game;

use App\Models\GameDuel;
use App\Models\GamePlayer;
use App\Models\GameSession;
use App\Models\Soal;
use App\Enums\GameStatus;
use Illuminate\Support\Facades\DB;

class DuelService
{
    public function __construct(
        private readonly DiceService $dice,
        private readonly MovementService $movement,
        private readonly TurnService $turn
    ) {}

    public function checkAndStartDuel(GameSession $gameSession, GamePlayer $challenger, array &$events): ?GameDuel
    {
        if ($gameSession->status === GameStatus::Finished) {
            return null;
        }

        if ($challenger->posisi_pion <= 0) {
            return null;
        }

        $opponent = GamePlayer::query()
            ->where('game_session_id', $gameSession->id)
            ->where('id', '!=', $challenger->id)
            ->where('posisi_pion', $challenger->posisi_pion)
            ->orderByDesc('updated_at')
            ->first();

        if (!$opponent) {
            return null;
        }

        $duel = DB::transaction(function () use ($gameSession, $challenger, $opponent) {
            $gameSession->update(['status' => GameStatus::Duel]);

            $duel = GameDuel::create([
                'game_session_id' => $gameSession->id,
                'challenger_id' => $challenger->id,
                'opponent_id' => $opponent->id,
                'status' => 'waiting',
                'started_at' => now(),
            ]);

            $soals = Soal::inRandomOrder()->limit(3)->get();
            
            foreach ($soals as $i => $soal) {
                $duel->questions()->create([
                    'soal_id' => $soal->id,
                    'order' => $i + 1,
                ]);

                // Auto answer for robots
                foreach ([$challenger, $opponent] as $player) {
                    if ($player->is_robot) {
                        $decider = app(\App\Services\Game\TurnDecider\RobotTurnDecider::class);
                        $jawaban = $decider->decideAnswer($gameSession, $player, $soal);
                        $isCorrect = $soal->kunci_jawaban === $jawaban;
                        $timeTaken = random_int(5000, 15000); // Simulated delay 5-15s

                        $duel->answers()->create([
                            'game_player_id' => $player->id,
                            'soal_id' => $soal->id,
                            'is_correct' => $isCorrect,
                            'time_taken_ms' => $timeTaken,
                        ]);
                    }
                }
            }

            return $duel;
        });

        $duel->load(['questions.soal']);

        return $duel;
    }

    public function submitDuelAnswer(GameSession $gameSession, GamePlayer $gamePlayer, int $soalId, ?string $jawaban, int $timeTakenMs): array
    {
        $duel = GameDuel::where('game_session_id', $gameSession->id)->where('status', 'waiting')->firstOrFail();
        
        // Ensure player is part of the duel
        if ($gamePlayer->id !== $duel->challenger_id && $gamePlayer->id !== $duel->opponent_id) {
            abort(403, 'Anda bukan bagian dari duel ini.');
        }

        $soal = Soal::findOrFail($soalId);
        $isCorrect = $soal->kunci_jawaban === $jawaban;

        DB::transaction(function () use ($duel, $gamePlayer, $soalId, $isCorrect, $timeTakenMs) {
            $duel->answers()->updateOrCreate(
                ['game_player_id' => $gamePlayer->id, 'soal_id' => $soalId],
                ['is_correct' => $isCorrect, 'time_taken_ms' => $timeTakenMs]
            );
        });

        // Check if both players have answered all 3 questions
        $answersCount = $duel->answers()->count();
        if ($answersCount >= 6) { // 2 players * 3 questions
            return $this->processDuelFinished($gameSession, $duel, $gamePlayer);
        }

        return [
            'type' => 'duel_answered',
            'session' => $gameSession->fresh(),
            'player' => $gamePlayer->fresh(),
            'benar' => $isCorrect,
            'pembahasan' => $soal->pembahasan,
            'kunci_jawaban' => $soal->kunci_jawaban,
        ];
    }

    private function processDuelFinished(GameSession $gameSession, GameDuel $duel, GamePlayer $gamePlayer): array
    {
        $result = DB::transaction(function () use ($gameSession, $duel, $gamePlayer) {
            $challengerCorrect = $duel->answers()->where('game_player_id', $duel->challenger_id)->where('is_correct', true)->count();
            $opponentCorrect = $duel->answers()->where('game_player_id', $duel->opponent_id)->where('is_correct', true)->count();

            $winnerId = null;
            $loserId = null;

            if ($challengerCorrect > $opponentCorrect) {
                $winnerId = $duel->challenger_id;
                $loserId = $duel->opponent_id;
            } elseif ($opponentCorrect > $challengerCorrect) {
                $winnerId = $duel->opponent_id;
                $loserId = $duel->challenger_id;
            } else {
                // Tie breaker on time
                $challengerTime = $duel->answers()->where('game_player_id', $duel->challenger_id)->where('is_correct', true)->sum('time_taken_ms');
                $opponentTime = $duel->answers()->where('game_player_id', $duel->opponent_id)->where('is_correct', true)->sum('time_taken_ms');

                if ($challengerTime < $opponentTime) {
                    $winnerId = $duel->challenger_id;
                    $loserId = $duel->opponent_id;
                } else if ($opponentTime < $challengerTime) {
                    $winnerId = $duel->opponent_id;
                    $loserId = $duel->challenger_id;
                } else {
                    // Exact tie, just give it to challenger
                    $winnerId = $duel->challenger_id;
                    $loserId = $duel->opponent_id;
                }
            }

            $penaltyRoll = $this->dice->roll($gameSession);
            $loser = GamePlayer::find($loserId);
            
            $duel->update([
                'status' => 'finished',
                'winner_id' => $winnerId,
                'loser_id' => $loserId,
                'loser_penalty_roll' => $penaltyRoll,
                'finished_at' => now(),
            ]);

            // Move loser backwards
            $newPosisi = max(1, $loser->posisi_pion - $penaltyRoll);
            $loser->update(['posisi_pion' => $newPosisi]);

            $gameSession->update(['status' => GameStatus::Playing]);
            $this->turn->advance($gameSession);

            return [
                'type' => 'duel_finished',
                'session' => $gameSession->fresh(),
                'player' => $gamePlayer->fresh(),
                'duel' => $duel->load(['winner', 'loser']),
            ];
        });

        // Broadcast event
        // event(new DuelFinished($gameSession, $duel));

        return $result;
    }
}
