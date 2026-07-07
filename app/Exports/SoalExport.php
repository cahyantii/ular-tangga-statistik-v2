<?php

namespace App\Exports;

use App\Models\Soal;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Format kolom sama persis dengan yang diharapkan SoalImport, sehingga file
 * hasil export bisa diedit lalu diimpor kembali (round-trip).
 */
class SoalExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection(): Collection
    {
        return Soal::query()->with('kategori')->orderBy('id')->get();
    }

    public function headings(): array
    {
        return ['kategori', 'pertanyaan', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'kunci_jawaban', 'pembahasan', 'is_active'];
    }

    public function map($soal): array
    {
        return [
            $soal->kategori->nama ?? '',
            $soal->pertanyaan,
            $soal->opsi_jawaban['A'] ?? '',
            $soal->opsi_jawaban['B'] ?? '',
            $soal->opsi_jawaban['C'] ?? '',
            $soal->opsi_jawaban['D'] ?? '',
            $soal->kunci_jawaban,
            $soal->pembahasan,
            $soal->is_active ? 1 : 0,
        ];
    }
}
