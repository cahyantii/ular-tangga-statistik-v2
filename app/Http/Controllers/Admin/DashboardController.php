<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly AdminDashboardService $dashboard)
    {
    }

    public function index(): View
    {
        $pemain = $this->dashboard->statistikPemain();
        $permainan = $this->dashboard->statistikPermainan();
        $soal = $this->dashboard->statistikSoal();
        $leaderboard = $this->dashboard->leaderboardRingkas(5);
        $gamesHarian = $this->dashboard->chartPermainanHarian(7);
        $akurasiSoal = $this->dashboard->chartAkurasiSoal();

        return view('admin.dashboard', [
            'pemain' => $pemain,
            'permainan' => $permainan,
            'soal' => $soal,
            'leaderboard' => $leaderboard,
            'chartData' => [
                'games_daily' => [
                    'labels' => collect($gamesHarian)->keys()
                        ->map(fn ($tanggal) => Carbon::parse($tanggal)->translatedFormat('d M'))
                        ->all(),
                    'values' => array_values($gamesHarian),
                ],
                'mode_distribution' => [
                    'labels' => ['Vs Robot', 'Multiplayer'],
                    'values' => [$permainan['total_vs_robot'], $permainan['total_multiplayer']],
                ],
                'akurasi_per_kategori' => [
                    'labels' => collect($akurasiSoal['per_kategori'])->pluck('kategori')->all(),
                    'values' => collect($akurasiSoal['per_kategori'])->pluck('akurasi')->all(),
                ],
                'soal_tersulit' => [
                    'labels' => collect($akurasiSoal['soal_tersulit'])
                        ->map(fn ($item) => Str::limit($item['pertanyaan'], 40))
                        ->all(),
                    'values' => collect($akurasiSoal['soal_tersulit'])->pluck('tingkat_kesalahan')->all(),
                ],
            ],
        ]);
    }
}
