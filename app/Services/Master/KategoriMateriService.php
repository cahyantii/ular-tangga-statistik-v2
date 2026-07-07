<?php

namespace App\Services\Master;

use App\Models\KategoriMateri;
use Illuminate\Validation\ValidationException;

/**
 * Soft-delete "restrict" harus ditegakkan di service layer (Tahap 16, keputusan
 * final) karena restrictOnDelete() di database hanya berlaku pada hard delete,
 * bukan soft-delete (UPDATE deleted_at).
 */
class KategoriMateriService
{
    public function delete(KategoriMateri $kategori): void
    {
        if ($kategori->materi()->exists()) {
            throw ValidationException::withMessages([
                'kategori' => "Kategori \"{$kategori->nama}\" masih memiliki materi aktif dan tidak bisa dihapus.",
            ]);
        }

        if ($kategori->soal()->exists()) {
            throw ValidationException::withMessages([
                'kategori' => "Kategori \"{$kategori->nama}\" masih memiliki soal aktif dan tidak bisa dihapus.",
            ]);
        }

        $kategori->delete();
    }
}
