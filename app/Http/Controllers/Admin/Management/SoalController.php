<?php

namespace App\Http\Controllers\Admin\Management;

use App\Exports\SoalExport;
use App\Http\Concerns\HandlesOptimisticLocking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSoalRequest;
use App\Http\Requests\Admin\UpdateSoalRequest;
use App\Models\KategoriMateri;
use App\Models\Soal;
use App\Services\Admin\AdminDashboardService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SoalController extends Controller
{
    use HandlesOptimisticLocking;

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $soal = Soal::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->with('kategori')
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->integer('kategori_id')))
            ->when($request->filled('search'), fn ($query) => $query->where('pertanyaan', 'like', '%'.$request->string('search')->toString().'%'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.management.soal.index', [
            'soalList' => $soal,
            'filter' => $filter,
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.management.soal.create', [
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function store(StoreSoalRequest $request): RedirectResponse
    {
        $soal = Soal::create($this->mapRequestToAttributes($request));

        $this->forgetSoalStatsCache();

        return redirect()->route('admin.management.soal.index')
            ->with('status', 'Soal berhasil dibuat.');
    }

    public function edit(Soal $soal): View
    {
        return view('admin.management.soal.edit', [
            'soal' => $soal,
            'version' => $this->currentVersion($soal),
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function update(UpdateSoalRequest $request, Soal $soal): RedirectResponse
    {
        $this->assertNotStale($soal, $request);

        $soal->update($this->mapRequestToAttributes($request));

        $this->forgetSoalStatsCache();

        return redirect()->route('admin.management.soal.index')
            ->with('status', 'Soal berhasil diperbarui.');
    }

    public function destroy(Soal $soal): RedirectResponse
    {
        $soal->delete();

        $this->forgetSoalStatsCache();

        return redirect()->route('admin.management.soal.index')
            ->with('status', 'Soal berhasil dihapus.');
    }

    public function restore(int $soal): RedirectResponse
    {
        $soal = Soal::onlyTrashed()->findOrFail($soal);
        $soal->restore();

        $this->forgetSoalStatsCache();

        return redirect()->route('admin.management.soal.index')
            ->with('status', 'Soal berhasil dipulihkan.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $format = $request->string('format', 'xlsx')->toString();
        $format = in_array($format, ['csv', 'xlsx'], true) ? $format : 'xlsx';

        $this->notifications->sendToAdmins($this->notifications->payloadExportPerformed('soal'));

        return Excel::download(new SoalExport(), 'soal-'.now()->format('Y-m-d').'.'.$format);
    }

    private function mapRequestToAttributes(Request $request): array
    {
        return [
            'kategori_id' => $request->integer('kategori_id'),
            'pertanyaan' => $request->string('pertanyaan')->toString(),
            'opsi_jawaban' => [
                'A' => $request->string('opsi_a')->toString(),
                'B' => $request->string('opsi_b')->toString(),
                'C' => $request->string('opsi_c')->toString(),
                'D' => $request->string('opsi_d')->toString(),
            ],
            'kunci_jawaban' => $request->string('kunci_jawaban')->toString(),
            'pembahasan' => $request->string('pembahasan')->toString(),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function forgetSoalStatsCache(): void
    {
        Cache::forget(AdminDashboardService::STATS_QUESTIONS_CACHE_KEY);
        Cache::forget(AdminDashboardService::CHART_QUESTIONS_ACCURACY_CACHE_KEY);
    }
}
