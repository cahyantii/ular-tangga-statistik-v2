<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Concerns\HandlesOptimisticLocking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePapanKonektorRequest;
use App\Http\Requests\Admin\UpdatePapanKonektorRequest;
use App\Models\PapanKonektor;
use App\Models\PapanPermainan;
use App\Services\Master\PapanKonektorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PapanKonektorController extends Controller
{
    use HandlesOptimisticLocking;

    public function __construct(private readonly PapanKonektorService $konektorService)
    {
    }

    public function index(PapanPermainan $papanPermainan): View
    {
        return view('admin.management.papan-permainan.konektor.index', [
            'papan' => $papanPermainan,
            'konektorList' => $papanPermainan->papanKonektor()->orderBy('posisi_awal')->get(),
        ]);
    }

    public function create(PapanPermainan $papanPermainan): View
    {
        return view('admin.management.papan-permainan.konektor.create', ['papan' => $papanPermainan]);
    }

    public function store(StorePapanKonektorRequest $request, PapanPermainan $papanPermainan): RedirectResponse
    {
        $this->konektorService->create($papanPermainan, $request->validated());

        return redirect()->route('admin.management.papan-permainan.konektor.index', $papanPermainan)
            ->with('status', 'Konektor berhasil ditambahkan.');
    }

    public function edit(PapanPermainan $papanPermainan, PapanKonektor $konektor): View
    {
        $this->assertBelongsToPapan($papanPermainan, $konektor);

        return view('admin.management.papan-permainan.konektor.edit', [
            'papan' => $papanPermainan,
            'konektor' => $konektor,
            'version' => $this->currentVersion($konektor),
        ]);
    }

    public function update(UpdatePapanKonektorRequest $request, PapanPermainan $papanPermainan, PapanKonektor $konektor): RedirectResponse
    {
        $this->assertBelongsToPapan($papanPermainan, $konektor);
        $this->assertNotStale($konektor, $request);

        $this->konektorService->update($papanPermainan, $konektor, $request->validated());

        return redirect()->route('admin.management.papan-permainan.konektor.index', $papanPermainan)
            ->with('status', 'Konektor berhasil diperbarui.');
    }

    public function destroy(PapanPermainan $papanPermainan, PapanKonektor $konektor): RedirectResponse
    {
        $this->assertBelongsToPapan($papanPermainan, $konektor);

        $this->konektorService->delete($papanPermainan, $konektor);

        return redirect()->route('admin.management.papan-permainan.konektor.index', $papanPermainan)
            ->with('status', 'Konektor berhasil dihapus.');
    }

    private function assertBelongsToPapan(PapanPermainan $papan, PapanKonektor $konektor): void
    {
        abort_unless($konektor->papan_id === $papan->id, 404);
    }
}
