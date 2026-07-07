<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateGameSettingsRequest;
use App\Models\GameSetting;
use App\Services\Master\GameSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GameSettingController extends Controller
{
    public function __construct(private readonly GameSettingService $gameSettingService)
    {
    }

    public function index(): View
    {
        $settingsByGroup = GameSetting::query()
            ->orderBy('group')
            ->orderBy('label')
            ->get()
            ->groupBy(fn (GameSetting $setting) => $setting->group ?? 'Lainnya');

        return view('admin.management.game-settings.index', [
            'settingsByGroup' => $settingsByGroup,
        ]);
    }

    public function update(UpdateGameSettingsRequest $request): RedirectResponse
    {
        $totalDiubah = $this->gameSettingService->bulkUpdate($request->validated()['settings'] ?? [], $request->user());

        $status = $totalDiubah > 0
            ? "{$totalDiubah} pengaturan berhasil diperbarui."
            : 'Tidak ada perubahan yang disimpan.';

        return redirect()->route('admin.management.game-settings.index')->with('status', $status);
    }
}
