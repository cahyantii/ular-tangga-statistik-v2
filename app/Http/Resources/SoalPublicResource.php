<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Sengaja TIDAK menyertakan `kunci_jawaban`/`pembahasan` — dipakai saat soal
 * baru dipresentasikan ke pemain, sebelum dijawab. Jangan gunakan resource ini
 * untuk respons setelah jawaban dikirim (gunakan array eksplisit yang memang
 * boleh membocorkan pembahasan/kunci di titik itu).
 */
class SoalPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pertanyaan' => $this->pertanyaan,
            'opsi_jawaban' => $this->opsi_jawaban,
        ];
    }
}
