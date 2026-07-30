<?php

namespace App\Http\Resources;

use App\Enums\GameStatus;
use App\Repositories\Game\GameSettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\GameSession
 */
class GameSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mode' => $this->mode->value,
            'room_id' => $this->room_id,
            'status' => $this->status->value,
            'total_turn' => $this->total_turn,
            'current_turn_game_player_id' => $this->current_turn_game_player_id,
            'winner_game_player_id' => $this->winner_game_player_id,
            'win_reason' => $this->win_reason?->value,
            'duration_seconds' => $this->duration_seconds,
            'active_question_expires_at' => $this->active_question_expires_at?->toIso8601String(),
            'active_question' => $this->when(
                $this->active_question_id !== null,
                fn () => new SoalPublicResource($this->activeQuestion)
            ),
            // Tahap 12c: batas waktu reconnect (untuk countdown 60 detik di UI,
            // Stage 4 keputusan final) — dihitung dari updated_at saat status
            // baru saja dijadikan Paused (lihat GameSessionService::pauseForDisconnect()),
            // bukan kolom baru di database.
            'reconnect_deadline_at' => $this->when(
                $this->status === GameStatus::Paused,
                fn () => $this->updated_at
                    ->copy()
                    ->addSeconds(app(GameSettingsRepository::class)->getInt('reconnect_timeout_seconds'))
                    ->toIso8601String()
            ),
            'active_duel' => $this->when(
                $this->status === GameStatus::Duel,
                fn () => tap(\App\Models\GameDuel::with(['questions.soal', 'answers.player'])
                    ->where('game_session_id', $this->id)
                    ->where('status', 'waiting')
                    ->first(), function ($duel) {
                        return $duel ? [
                            'id' => $duel->id,
                            'challenger_id' => $duel->challenger_id,
                            'opponent_id' => $duel->opponent_id,
                            'status' => $duel->status,
                            'questions' => $duel->questions->map(function (\App\Models\GameDuelQuestion $q) {
                                return [
                                    'id' => $q->id,
                                    'order' => $q->order,
                                    'soal' => new SoalPublicResource($q->soal)
                                ];
                            })->values()->all(),
                            'answers' => $duel->answers->map(function (\App\Models\GameDuelAnswer $a) {
                                $isRobot = $a->player->is_robot ?? false;
                                return [
                                    'game_player_id' => $a->game_player_id,
                                    'soal_id' => $a->soal_id,
                                    'is_robot' => $isRobot,
                                    'is_correct' => $isRobot ? $a->is_correct : null,
                                    'time_taken_ms' => $isRobot ? $a->time_taken_ms : null,
                                ];
                            })->values()->all()
                        ] : null;
                    })
            ),
            'players' => GamePlayerResource::collection($this->whenLoaded('players')),
        ];
    }
}
