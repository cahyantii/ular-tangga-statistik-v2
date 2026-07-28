<?php

namespace App\Services\Game;

use App\Models\GamePlayer;
use App\Models\GameSession;

class PowerUpService
{
    /**
     * Dapatkan daftar semua power up yang tersedia beserta propertinya
     */
    public static function getAvailablePowerUps(): array
    {
        return [
            'double_dice' => [
                'id' => 'double_dice',
                'name' => 'Dadu Ganda',
                'description' => 'Hasil dadu dikali 2 di giliran ini. Dapat 6? Melaju 12 langkah!',
                'type' => 'hold',
                'icon' => '🎲🎲',
            ],
            'snake_shield' => [
                'id' => 'snake_shield',
                'name' => 'Perisai Ular',
                'description' => 'Melindungi dari 1x gigitan ular.',
                'type' => 'hold',
                'icon' => '🛡️',
            ],
            'teleport_forward' => [
                'id' => 'teleport_forward',
                'name' => 'Teleport Maju',
                'description' => 'Otomatis maju 3 petak.',
                'type' => 'immediate',
                'icon' => '🚀',
            ],
            'curse_dice' => [
                'id' => 'curse_dice',
                'name' => 'Kutukan Dadu',
                'description' => 'Lawan selanjutnya maksimal hanya dapat dadu angka 3.',
                'type' => 'hold',
                'icon' => '☠️',
            ]
        ];
    }

    /**
     * Gunakan item HOLD dari inventory pemain
     */
    public function useItem(GameSession $session, GamePlayer $player, string $itemId): array
    {
        $inventory = $player->inventory ?? [];
        
        $itemIndex = array_search($itemId, $inventory);
        if ($itemIndex === false) {
            throw new \Exception("Item tidak ditemukan di inventory.");
        }

        // Hapus item dari inventory
        array_splice($inventory, $itemIndex, 1);
        $player->inventory = $inventory;
        
        $activeBuffs = $player->active_buffs ?? [];

        $message = "";

        switch ($itemId) {
            case 'double_dice':
                if (!in_array('double_dice', $activeBuffs)) {
                    $activeBuffs[] = 'double_dice';
                }
                $message = "menggunakan Dadu Ganda!";
                break;
            case 'snake_shield':
                if (!in_array('snake_shield', $activeBuffs)) {
                    $activeBuffs[] = 'snake_shield';
                }
                $message = "mengaktifkan Perisai Ular!";
                break;
            case 'curse_dice':
                $this->applyCurseToOpponents($session, $player);
                $message = "mengutuk dadu lawan!";
                break;
            default:
                throw new \Exception("Item tidak dapat digunakan.");
        }

        $player->active_buffs = $activeBuffs;
        $player->save();

        return [
            'success' => true,
            'message' => $message
        ];
    }

    /**
     * Terapkan kutukan ke lawan selanjutnya
     */
    private function applyCurseToOpponents(GameSession $session, GamePlayer $caster): void
    {
        // Cari pemain selanjutnya
        $allPlayers = $session->players()->orderBy('turn_order')->get();
        $nextPlayer = null;
        
        $foundCaster = false;
        foreach ($allPlayers as $p) {
            if ($foundCaster && $p->status->value === 'playing') {
                $nextPlayer = $p;
                break;
            }
            if ($p->id === $caster->id) {
                $foundCaster = true;
            }
        }
        
        // Wrap around
        if (!$nextPlayer) {
            foreach ($allPlayers as $p) {
                if ($p->status->value === 'playing' && $p->id !== $caster->id) {
                    $nextPlayer = $p;
                    break;
                }
            }
        }

        if ($nextPlayer) {
            $buffs = $nextPlayer->active_buffs ?? [];
            if (!in_array('cursed_dice', $buffs)) {
                $buffs[] = 'cursed_dice';
                $nextPlayer->active_buffs = $buffs;
                $nextPlayer->save();
            }
        }
    }
}
