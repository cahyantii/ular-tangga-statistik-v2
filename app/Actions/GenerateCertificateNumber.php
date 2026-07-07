<?php

namespace App\Actions;

use App\Models\Certificate;

class GenerateCertificateNumber
{
    /**
     * Generate nomor sertifikat unik dengan format CERT-{tahun}-{urutan 6 digit}.
     */
    public function __invoke(): string
    {
        $tahun = now()->format('Y');
        $prefix = "CERT-{$tahun}-";

        $terakhir = Certificate::where('nomor_sertifikat', 'like', "{$prefix}%")
            ->orderByDesc('nomor_sertifikat')
            ->value('nomor_sertifikat');

        $urutan = $terakhir ? ((int) substr($terakhir, -6)) + 1 : 1;

        return $prefix . str_pad((string) $urutan, 6, '0', STR_PAD_LEFT);
    }
}
