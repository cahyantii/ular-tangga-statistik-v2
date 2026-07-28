<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'skor' => $this->skor,
            'accuracy' => $this->accuracy,
            'status' => $this->status->value,
            'active_buffs' => $this->active_buffs ?? [],
            'inventory' => $this->whenLoaded('user', function () use ($request) {
                // Jangan sembunyikan item kita sendiri
                if ($this->user_id === $request->user()?->id) {
                    return $this->inventory ?? [];
                }
                
                // Untuk lawan, kembalikan 'hidden' sebanyak item yang dia punya
                $inv = $this->inventory ?? [];
                return array_fill(0, count($inv), 'hidden');
            }, function () use ($request) {
                // Jika relasi user tidak di-load (misal dari state session yang disederhanakan), tetap lakukan hal yang sama
                if ($this->user_id === $request->user()?->id) {
                    return $this->inventory ?? [];
                }
                $inv = $this->inventory ?? [];
                return array_fill(0, count($inv), 'hidden');
            }),
        ];
    }
}
