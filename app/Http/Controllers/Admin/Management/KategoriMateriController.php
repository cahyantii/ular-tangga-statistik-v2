<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKategoriMateriRequest;
use App\Http\Requests\Admin\UpdateKategoriMateriRequest;
use App\Models\KategoriMateri;
use App\Services\Master\KategoriMateriService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KategoriMateriController extends Controller
{
    public function __construct(private readonly KategoriMateriService $kategoriMateriService)
    {
    }

    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $kategori = KategoriMateri::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->withCount(['materi', 'soal'])
            ->orderBy('urutan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.management.kategori-materi.index', [
            'kategoriList' => $kategori,
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.management.kategori-materi.create');
    }

    public function store(StoreKategoriMateriRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['nama']);
        $data['is_active'] = $request->boolean('is_active');

        $kategori = KategoriMateri::create($data);

        return redirect()->route('admin.management.kategori-materi.index')
            ->with('status', "Kategori \"{$kategori->nama}\" berhasil dibuat.");
    }

    public function edit(KategoriMateri $kategoriMateri): View
    {
        return view('admin.management.kategori-materi.edit', ['kategori' => $kategoriMateri]);
    }

    public function update(UpdateKategoriMateriRequest $request, KategoriMateri $kategoriMateri): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = ($data['slug'] ?? null) ?: Str::slug($data['nama']);
        $data['is_active'] = $request->boolean('is_active');

        $kategoriMateri->update($data);

        return redirect()->route('admin.management.kategori-materi.index')
            ->with('status', "Kategori \"{$kategoriMateri->nama}\" berhasil diperbarui.");
    }

    public function destroy(KategoriMateri $kategoriMateri): RedirectResponse
    {
        $nama = $kategoriMateri->nama;

        $this->kategoriMateriService->delete($kategoriMateri);

        return redirect()->route('admin.management.kategori-materi.index')
            ->with('status', "Kategori \"{$nama}\" berhasil dihapus.");
    }

    public function restore(int $kategoriMateri): RedirectResponse
    {
        $kategori = KategoriMateri::onlyTrashed()->findOrFail($kategoriMateri);
        $kategori->restore();

        return redirect()->route('admin.management.kategori-materi.index')
            ->with('status', "Kategori \"{$kategori->nama}\" berhasil dipulihkan.");
    }
}
