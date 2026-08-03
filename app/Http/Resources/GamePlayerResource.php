<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\GamePlayer
 */
class GamePlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nama' => $this->is_robot ? 'Robot' : $this->user?->name,
            'is_robot' => $this->is_robot,
            'turn_order' => $this->turn_order,
            'pawn_color' => $this->pawn_color,
            'pawn_icon' => $this->pawn_icon,
            'posisi_pion' => $this->posisi_pion,
            'finish_rank' => $this->finish_rank,
            'finished_at_turn' => $this->finished_at_turn,
            'skor' => $this->skor,
            'accuracy' => $this->accuracy,
            'status' => $this->status->value,
        ];
    }
}
