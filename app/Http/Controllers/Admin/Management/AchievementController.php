<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAchievementRequest;
use App\Http\Requests\Admin\UpdateAchievementRequest;
use App\Models\Achievement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AchievementController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'active')->toString();

        $achievements = Achievement::query()
            ->when($filter === 'trashed', fn ($query) => $query->onlyTrashed())
            ->when($filter === 'all', fn ($query) => $query->withTrashed())
            ->orderBy('urutan')
            ->paginate(15)
            ->withQueryString();

        return view('admin.management.achievements.index', [
            'achievements' => $achievements,
            'filter' => $filter,
        ]);
    }

    public function create(): View
    {
        return view('admin.management.achievements.create');
    }

    public function store(StoreAchievementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $achievement = Achievement::create($data);

        return redirect()->route('admin.management.achievements.index')
            ->with('status', "Achievement \"{$achievement->nama}\" berhasil dibuat.");
    }

    public function edit(Achievement $achievement): View
    {
        return view('admin.management.achievements.edit', ['achievement' => $achievement]);
    }

    public function update(UpdateAchievementRequest $request, Achievement $achievement): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $achievement->update($data);

        return redirect()->route('admin.management.achievements.index')
            ->with('status', "Achievement \"{$achievement->nama}\" berhasil diperbarui.");
    }

    public function destroy(Achievement $achievement): RedirectResponse
    {
        $nama = $achievement->nama;
        $achievement->delete();

        return redirect()->route('admin.management.achievements.index')
            ->with('status', "Achievement \"{$nama}\" berhasil dihapus.");
    }

    public function restore(int $achievement): RedirectResponse
    {
        $achievement = Achievement::onlyTrashed()->findOrFail($achievement);
        $achievement->restore();

        return redirect()->route('admin.management.achievements.index')
            ->with('status', "Achievement \"{$achievement->nama}\" berhasil dipulihkan.");
    }
}
