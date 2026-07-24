<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Concerns\HandlesOptimisticLocking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePapanPermainanRequest;
use App\Http\Requests\Admin\UpdatePapanPermainanRequest;
use App\Exports\PapanPetakExport;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Services\Master\PapanExportService;
use App\Services\Master\PapanPermainanService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PapanPermainanController extends Controller
{
    use HandlesOptimisticLocking;

    public function __construct(
        private readonly PapanPermainanService $papanService,
        private readonly PapanExportService $exportService,
        private readonly NotificationService $notifications,
    ) {
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $papanList = PapanPermainan::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->withCount('papanKonektor')
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.management.papan-permainan.index', [
            'papanList' => $papanList,
            'filter' => $filter,
            'stats' => [
                'papan' => PapanPermainan::query()->count(),
                'petak' => (int) PapanPermainan::query()->sum('jumlah_petak'),
                'konektor' => PapanKonektor::query()->whereHas('papan')->count(),
                'aktif' => PapanPermainan::query()->active()->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.management.papan-permainan.create');
    }

    public function store(StorePapanPermainanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $papan = $this->papanService->create($data);

        return redirect()->route('admin.management.papan-permainan.petak.index', $papan)
            ->with('status', "Papan \"{$papan->nama}\" berhasil dibuat. Silakan atur petaknya di bawah ini.");
    }

    public function edit(PapanPermainan $papanPermainan): View
    {
        return view('admin.management.papan-permainan.edit', [
            'papan' => $papanPermainan,
            'version' => $this->currentVersion($papanPermainan),
        ]);
    }

    public function update(UpdatePapanPermainanRequest $request, PapanPermainan $papanPermainan): RedirectResponse
    {
        $this->assertNotStale($papanPermainan, $request);

        $papanPermainan->update([
            'nama' => $request->string('nama')->toString(),
            'deskripsi' => $request->string('deskripsi')->toString() ?: null,
            'jumlah_kolom' => $request->integer('jumlah_kolom'),
            'thumbnail' => $request->string('thumbnail')->toString() ?: null,
        ]);

        return redirect()->route('admin.management.papan-permainan.index')
            ->with('status', "Papan \"{$papanPermainan->nama}\" berhasil diperbarui.");
    }

    public function destroy(PapanPermainan $papanPermainan): RedirectResponse
    {
        $nama = $papanPermainan->nama;
        $this->papanService->delete($papanPermainan);

        return redirect()->route('admin.management.papan-permainan.index')
            ->with('status', "Papan \"{$nama}\" berhasil dihapus.");
    }

    public function restore(int $papanPermainan): RedirectResponse
    {
        $papan = PapanPermainan::onlyTrashed()->findOrFail($papanPermainan);
        $papan->restore();

        return redirect()->route('admin.management.papan-permainan.index')
            ->with('status', "Papan \"{$papan->nama}\" berhasil dipulihkan.");
    }

    public function toggleActive(PapanPermainan $papanPermainan): RedirectResponse
    {
        if ($papanPermainan->is_active) {
            $this->papanService->deactivate($papanPermainan);
            $status = "Papan \"{$papanPermainan->nama}\" dinonaktifkan.";
        } else {
            $this->papanService->activate($papanPermainan);
            $status = "Papan \"{$papanPermainan->nama}\" diaktifkan.";
        }

        return redirect()->route('admin.management.papan-permainan.index')->with('status', $status);
    }

    public function export(Request $request, PapanPermainan $papanPermainan): StreamedResponse|BinaryFileResponse
    {
        $format = $request->string('format', 'json')->toString();
        $format = in_array($format, ['json', 'xlsx', 'csv'], true) ? $format : 'json';

        $this->notifications->sendToAdmins($this->notifications->payloadExportPerformed('papan permainan'));

        if ($format === 'json') {
            return $this->exportService->downloadJson($papanPermainan);
        }

        $filename = 'papan-'.$papanPermainan->id.'-petak-'.now()->format('Y-m-d').'.'.$format;

        return Excel::download(new PapanPetakExport($papanPermainan), $filename);
    }
}
