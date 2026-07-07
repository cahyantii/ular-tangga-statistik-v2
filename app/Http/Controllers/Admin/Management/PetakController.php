<?php

namespace App\Http\Controllers\Admin\Management;

use App\Enums\TileType;
use App\Http\Concerns\HandlesOptimisticLocking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePetakRequest;
use App\Models\KategoriMateri;
use App\Models\PapanPermainan;
use App\Models\Petak;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PetakController extends Controller
{
    use HandlesOptimisticLocking;

    public function index(PapanPermainan $papanPermainan): View
    {
        $papanPermainan->load(['petak', 'papanKonektor']);

        return view('admin.management.papan-permainan.petak.index', [
            'papan' => $papanPermainan,
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

    public function update(UpdatePetakRequest $request, PapanPermainan $papanPermainan, Petak $petak): RedirectResponse
    {
        $this->assertBelongsToPapan($papanPermainan, $petak);
        $this->assertEditable($papanPermainan, $petak);
        $this->assertNotStale($petak, $request);

        $data = $request->validated();
        unset($data['_version']);
        $data['kategori_id'] = $data['jenis_petak'] === 'soal' ? $data['kategori_id'] : null;

        $petak->update($data);

        return redirect()->route('admin.management.papan-permainan.petak.index', $papanPermainan)
            ->with('status', "Petak #{$petak->posisi} berhasil diperbarui.");
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
