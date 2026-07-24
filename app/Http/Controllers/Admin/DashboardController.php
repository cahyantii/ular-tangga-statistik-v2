<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const MAKS_HARI_PERIODE = 90;

    public function __construct(private readonly AdminDashboardService $dashboard)
    {
    }

    public function index(Request $request): View
    {
        [$dari, $sampai] = $this->resolvePeriode($request);
        $hari = $this->hariInklusif($dari, $sampai);

        $pemain = $this->dashboard->statistikPemain();
        $permainan = $this->dashboard->statistikPermainan();
        $soal = $this->dashboard->statistikSoal();
        $leaderboard = $this->dashboard->leaderboardRingkas(5);
        $gamesHarian = $this->dashboard->chartPermainanHarian($hari, $sampai);
        $akurasiSoal = $this->dashboard->chartAkurasiSoal();
        $achievementTerbaru = $this->dashboard->achievementTerbaru($hari, $sampai, 5);

        return view('admin.dashboard', [
            'pemain' => $pemain,
            'permainan' => $permainan,
            'soal' => $soal,
            'leaderboard' => $leaderboard,
            'achievementTerbaru' => $achievementTerbaru,
            'hariPeriode' => $hari,
            'periode' => [
                'mulai' => $dari,
                'selesai' => $sampai,
            ],
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

    /**
     * Baca filter tanggal (?from=&to=) dari query string date-range picker
     * di dashboard. Jatuh kembali ke "7 hari terakhir" kalau parameter
     * kosong/tidak valid, dan dibatasi maksimal 90 hari agar query chart
     * harian tetap ringan.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolvePeriode(Request $request): array
    {
        $default = [now()->subDays(6)->startOfDay(), now()->endOfDay()];

        $dariInput = $request->query('from');
        $sampaiInput = $request->query('to');

        if (! $dariInput || ! $sampaiInput) {
            return $default;
        }

        try {
            $dari = Carbon::parse($dariInput)->startOfDay();
            $sampai = Carbon::parse($sampaiInput)->endOfDay();
        } catch (\Exception) {
            return $default;
        }

        if ($sampai->greaterThan(now()->endOfDay())) {
            $sampai = now()->endOfDay();
        }

        if ($dari->greaterThan($sampai)) {
            return $default;
        }

        if ($this->hariInklusif($dari, $sampai) > self::MAKS_HARI_PERIODE) {
            $dari = $sampai->copy()->subDays(self::MAKS_HARI_PERIODE - 1)->startOfDay();
        }

        return [$dari, $sampai];
    }

    /**
     * Jumlah hari inklusif antara dua tanggal (tanpa memandang jam),
     * dihitung dari titik tengah malam masing-masing agar bebas dari
     * pembulatan pecahan hari akibat endOfDay().
     */
    private function hariInklusif(Carbon $dari, Carbon $sampai): int
    {
        return max(1, $dari->copy()->startOfDay()->diffInDays($sampai->copy()->startOfDay()) + 1);
    }
}
