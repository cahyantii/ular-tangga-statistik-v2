<?php

namespace App\Http\Controllers\Admin\Management;

use App\Enums\TileType;
use App\Exports\PapanPetakExport;
use App\Http\Concerns\HandlesOptimisticLocking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePetakRequest;
use App\Models\KategoriMateri;
use App\Models\PapanPermainan;
use App\Models\Petak;
use App\Services\Notification\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PetakController extends Controller
{
    use HandlesOptimisticLocking;

    public function __construct(private readonly NotificationService $notifications)
    {
    }

    public function index(PapanPermainan $papanPermainan): View
    {
        $papanPermainan->load(['petak', 'papanKonektor']);

        return view('admin.management.papan-permainan.petak.index', [
            'papan' => $papanPermainan,
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function edit(PapanPermainan $papanPermainan, Petak $petak): View
    {
        $this->assertBelongsToPapan($papanPermainan, $petak);
        $this->assertEditable($papanPermainan, $petak);

        return view('admin.management.papan-permainan.petak.edit', [
            'papan' => $papanPermainan,
            'petak' => $petak,
            'version' => $this->currentVersion($petak),
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function update(UpdatePetakRequest $request, PapanPermainan $papanPermainan, Petak $petak): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToPapan($papanPermainan, $petak);
        $this->assertEditable($papanPermainan, $petak);
        $this->assertNotStale($petak, $request);

        $data = $request->validated();
        unset($data['_version']);
        $data['kategori_id'] = null; // Petak soal dihapus, kategori_id selalu null
        $data['is_active'] = $request->boolean('is_active', true);

        $petak->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => "Petak #{$petak->posisi} berhasil diperbarui.",
                'petak' => $petak->only(['id', 'posisi', 'jenis_petak', 'is_active', 'kategori_id', 'label', 'icon', 'warna', 'border_warna', 'deskripsi']),
                'version' => $this->currentVersion($petak),
            ]);
        }

        return redirect()->route('admin.management.papan-permainan.petak.index', $papanPermainan)
            ->with('status', "Petak #{$petak->posisi} berhasil diperbarui.");
    }

    public function export(Request $request, PapanPermainan $papanPermainan): BinaryFileResponse
    {
        $format = $request->string('format', 'xlsx')->toString();
        $format = in_array($format, ['xlsx', 'csv'], true) ? $format : 'xlsx';

        $filename = 'papan-'.$papanPermainan->id.'-petak-'.now()->format('Y-m-d').'.'.$format;

        $this->notifications->sendToAdmins($this->notifications->payloadExportPerformed('petak'));

        return Excel::download(new PapanPetakExport($papanPermainan), $filename);
    }

    private function assertBelongsToPapan(PapanPermainan $papan, Petak $petak): void
    {
        abort_unless($petak->papan_id === $papan->id, 404);
    }

    private function assertEditable(PapanPermainan $papan, Petak $petak): void
    {
        abort_if(
            in_array($petak->posisi, [1, $papan->jumlah_petak], true),
            403,
            'Petak Start/Finish tidak bisa diubah.'
        );

        abort_if(
            in_array($petak->jenis_petak, [TileType::Tangga, TileType::Ular], true),
            403,
            'Petak ini dikelola melalui halaman Konektor. Hapus konektornya terlebih dahulu untuk mengubah petak ini.'
        );
    }
}
