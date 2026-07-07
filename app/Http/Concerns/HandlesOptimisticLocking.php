<?php

namespace App\Http\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Optimistic locking sederhana: field tersembunyi `_version` menyimpan
 * `updated_at` (unix timestamp) milik record saat form dibuka. Jika record
 * sudah berubah oleh admin lain sebelum form ini disimpan, tolak perubahan
 * (Tahap 16, keputusan final — diterapkan pada Soal, Papan Permainan/Petak/
 * Konektor, dan Game Settings).
 */
trait HandlesOptimisticLocking
{
    protected function currentVersion(Model $model): string
    {
        return (string) $model->updated_at?->timestamp;
    }

    protected function assertNotStale(Model $model, Request $request): void
    {
        if ($request->input('_version') !== $this->currentVersion($model)) {
            throw ValidationException::withMessages([
                '_version' => 'Data ini sudah diperbarui oleh admin lain sejak Anda membuka form ini. Silakan muat ulang halaman dan coba lagi.',
            ]);
        }
    }
}
