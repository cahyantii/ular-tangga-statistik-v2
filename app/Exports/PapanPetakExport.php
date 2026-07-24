<?php

namespace App\Exports;

use App\Models\PapanPermainan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Export petak satu papan untuk diedit massal lewat spreadsheet lalu diimpor
 * kembali (round-trip). Hanya petak yang bisa diedit (biasa/soal/bonus/
 * penalti/mystery) yang berarti diubah lewat import ini — baris Start/Finish/
 * Tangga/Ular tetap ikut ter-export untuk konteks, tapi ditolak saat import
 * (dikelola lewat Editor Petak/Konektor, bukan bulk import).
 */
class PapanPetakExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly PapanPermainan $papan)
    {
    }

    public function collection(): Collection
    {
        return $this->papan->petak()->with('kategori')->get();
    }

    public function headings(): array
    {
        return ['papan_id', 'papan_nama', 'posisi', 'jenis_petak', 'kategori', 'label', 'icon', 'warna', 'border_warna', 'deskripsi'];
    }

    public function map($petak): array
    {
        return [
            $this->papan->id,
            $this->papan->nama,
            $petak->posisi,
            $petak->jenis_petak->value,
            $petak->kategori->nama ?? '',
            $petak->label,
            $petak->icon,
            $petak->warna,
            $petak->border_warna,
            $petak->deskripsi,
        ];
    }
}
