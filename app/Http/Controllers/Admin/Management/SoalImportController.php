<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use App\Services\Master\SoalImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SoalImportController extends Controller
{
    private const TEMP_DISK = 'local';

    private const TEMP_DIRECTORY = 'imports/soal';

    public function __construct(private readonly SoalImportService $importService)
    {
    }

    public function create(): View
    {
        return view('admin.management.soal.import');
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        $token = (string) Str::uuid();
        $extension = $request->file('file')->getClientOriginalExtension();
        $storedPath = $request->file('file')->storeAs(self::TEMP_DIRECTORY, "{$token}.{$extension}", self::TEMP_DISK);

        $result = $this->importService->parseAndValidate(Storage::disk(self::TEMP_DISK)->path($storedPath));

        return view('admin.management.soal.import-preview', [
            'valid' => $result['valid'],
            'invalid' => $result['invalid'],
            'token' => $token,
            'extension' => $extension,
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'extension' => ['required', 'string'],
        ]);

        $storedPath = self::TEMP_DIRECTORY.'/'.$request->string('token').'.'.$request->string('extension');

        if (! Storage::disk(self::TEMP_DISK)->exists($storedPath)) {
            return redirect()->route('admin.management.soal.import.create')
                ->withErrors(['file' => 'Sesi import sudah kedaluwarsa. Silakan unggah ulang file.']);
        }

        $result = $this->importService->parseAndValidate(Storage::disk(self::TEMP_DISK)->path($storedPath));
        $inserted = $this->importService->commit($result['valid']);

        Storage::disk(self::TEMP_DISK)->delete($storedPath);

        Cache::forget(AdminDashboardService::STATS_QUESTIONS_CACHE_KEY);
        Cache::forget(AdminDashboardService::CHART_QUESTIONS_ACCURACY_CACHE_KEY);

        return redirect()->route('admin.management.soal.index')
            ->with('status', "{$inserted} soal berhasil diimpor.");
    }
}
