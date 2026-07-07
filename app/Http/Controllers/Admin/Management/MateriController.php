<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MateriRequest;
use App\Models\KategoriMateri;
use App\Models\Materi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MateriController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $materi = Materi::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->with('kategori')
            ->when($request->filled('kategori_id'), fn ($query) => $query->where('kategori_id', $request->integer('kategori_id')))
            ->orderBy('kategori_id')
            ->orderBy('urutan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.management.materi.index', [
            'materiList' => $materi,
            'filter' => $filter,
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.management.materi.create', [
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function store(MateriRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $materi = Materi::create($data);

        return redirect()->route('admin.management.materi.index')
            ->with('status', "Materi \"{$materi->judul}\" berhasil dibuat.");
    }

    public function edit(Materi $materi): View
    {
        return view('admin.management.materi.edit', [
            'materi' => $materi,
            'kategoriOptions' => KategoriMateri::query()->active()->orderBy('urutan')->pluck('nama', 'id'),
        ]);
    }

    public function update(MateriRequest $request, Materi $materi): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $materi->update($data);

        return redirect()->route('admin.management.materi.index')
            ->with('status', "Materi \"{$materi->judul}\" berhasil diperbarui.");
    }

    public function destroy(Materi $materi): RedirectResponse
    {
        $judul = $materi->judul;
        $materi->delete();

        return redirect()->route('admin.management.materi.index')
            ->with('status', "Materi \"{$judul}\" berhasil dihapus.");
    }

    public function restore(int $materi): RedirectResponse
    {
        $materi = Materi::onlyTrashed()->findOrFail($materi);
        $materi->restore();

        return redirect()->route('admin.management.materi.index')
            ->with('status', "Materi \"{$materi->judul}\" berhasil dipulihkan.");
    }
}
