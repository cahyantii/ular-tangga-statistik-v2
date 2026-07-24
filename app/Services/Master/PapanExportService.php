<?php

namespace App\Services\Master;

use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PapanExportService
{
    public function downloadJson(PapanPermainan $papan): StreamedResponse
    {
        $papan->load(['petak.kategori', 'papanKonektor']);

        $data = [
            'papan' => [
                'nama' => $papan->nama,
                'deskripsi' => $papan->deskripsi,
                'jumlah_petak' => $papan->jumlah_petak,
                'jumlah_kolom' => $papan->jumlah_kolom,
                'thumbnail' => $papan->thumbnail,
                'is_active' => $papan->is_active,
            ],
            'petak' => $papan->petak->map(fn (Petak $p) => [
                'posisi' => $p->posisi,
                'jenis_petak' => $p->jenis_petak->value,
                'label' => $p->label,
                'icon' => $p->icon,
                'warna' => $p->warna,
                'border_warna' => $p->border_warna,
                'deskripsi' => $p->deskripsi,
                'kategori_nama' => $p->kategori?->nama,
            ])->values()->all(),
            'konektor' => $papan->papanKonektor->map(fn (PapanKonektor $k) => [
                'jenis' => $k->jenis->value,
                'posisi_awal' => $k->posisi_awal,
                'posisi_akhir' => $k->posisi_akhir,
                'label' => $k->label,
                'icon' => $k->icon,
            ])->values()->all(),
        ];

        $filename = 'papan-'.Str::slug($papan->nama).'-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}
