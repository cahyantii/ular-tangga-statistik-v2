<?php

namespace Database\Seeders;

use App\Models\KategoriMateri;
use Illuminate\Database\Seeder;

class KategoriMateriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoris = [
            [
                'nama' => 'Statistika Dasar',
                'slug' => 'statistika-dasar',
                'urutan' => 1,
            ],
            [
                'nama' => 'Pengenalan BPS',
                'slug' => 'pengenalan-bps',
                'urutan' => 2,
            ],
            [
                'nama' => 'Indikator Statistik',
                'slug' => 'indikator-statistik',
                'urutan' => 3,
            ],
        ];

        foreach ($kategoris as $kategori) {
            KategoriMateri::updateOrCreate(
                ['slug' => $kategori['slug']],
                [
                    'nama' => $kategori['nama'],
                    'urutan' => $kategori['urutan'],
                    'is_active' => true,
                ]
            );
        }
    }
}
