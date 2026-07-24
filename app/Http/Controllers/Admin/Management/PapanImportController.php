<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Services\Master\PapanImportService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PapanImportController extends Controller
{
    private const TEMP_DISK = 'local';

    private const TEMP_DIRECTORY = 'imports/papan-permainan';

    public function __construct(
        private readonly PapanImportService $importService,
        private readonly NotificationService $notifications,
    ) {
    }

    public function create(): View
    {
        return view('admin.management.papan-permainan.import');
    }

    public function preview(Request $request): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:json,txt'],
        ]);

        $token = (string) Str::uuid();
        $extension = $request->file('file')->getClientOriginalExtension();
        $storedPath = $request->file('file')->storeAs(self::TEMP_DIRECTORY, "{$token}.{$extension}", self::TEMP_DISK);

        $result = $this->importService->parseAndValidate(Storage::disk(self::TEMP_DISK)->path($storedPath));

        return view('admin.management.papan-permainan.import-preview', [
            ...$result,
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
            return redirect()->route('admin.management.papan-permainan.import.create')
                ->withErrors(['file' => 'Sesi import sudah kedaluwarsa. Silakan unggah ulang file.']);
        }

        $result = $this->importService->parseAndValidate(Storage::disk(self::TEMP_DISK)->path($storedPath));

        if ($result['papan_errors'] !== [] || $result['petak_errors'] !== []) {
            Storage::disk(self::TEMP_DISK)->delete($storedPath);

            return redirect()->route('admin.management.papan-permainan.import.create')
                ->withErrors(['file' => 'Data papan/petak tidak valid, import dibatalkan. Silakan periksa kembali file Anda.']);
        }

        $papan = $this->importService->commit($result);

        Storage::disk(self::TEMP_DISK)->delete($storedPath);

        $this->notifications->sendToAdmins($this->notifications->payloadImportSuccess('papan permainan', 1));

        return redirect()->route('admin.management.papan-permainan.petak.index', $papan)
            ->with('status', "Papan \"{$papan->nama}\" berhasil diimpor. Silakan periksa petaknya di bawah ini.");
    }
}
