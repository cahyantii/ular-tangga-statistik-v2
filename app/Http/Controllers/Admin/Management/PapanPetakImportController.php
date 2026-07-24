<?php

namespace App\Http\Controllers\Admin\Management;

use App\Http\Controllers\Controller;
use App\Models\PapanPermainan;
use App\Services\Master\PapanPetakImportService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PapanPetakImportController extends Controller
{
    private const TEMP_DISK = 'local';

    private const TEMP_DIRECTORY = 'imports/papan-petak';

    public function __construct(
        private readonly PapanPetakImportService $importService,
        private readonly NotificationService $notifications,
    ) {
    }

    public function create(PapanPermainan $papanPermainan): View
    {
        return view('admin.management.papan-permainan.petak.import', ['papan' => $papanPermainan]);
    }

    public function preview(Request $request, PapanPermainan $papanPermainan): View
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx'],
        ]);

        $token = (string) Str::uuid();
        $extension = $request->file('file')->getClientOriginalExtension();
        $storedPath = $request->file('file')->storeAs(self::TEMP_DIRECTORY, "{$token}.{$extension}", self::TEMP_DISK);

        $result = $this->importService->parseAndValidate($papanPermainan, Storage::disk(self::TEMP_DISK)->path($storedPath));

        return view('admin.management.papan-permainan.petak.import-preview', [
            'papan' => $papanPermainan,
            'valid' => $result['valid'],
            'invalid' => $result['invalid'],
            'token' => $token,
            'extension' => $extension,
        ]);
    }

    public function confirm(Request $request, PapanPermainan $papanPermainan): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'extension' => ['required', 'string'],
        ]);

        $storedPath = self::TEMP_DIRECTORY.'/'.$request->string('token').'.'.$request->string('extension');

        if (! Storage::disk(self::TEMP_DISK)->exists($storedPath)) {
            return redirect()->route('admin.management.papan-permainan.petak.import.create', $papanPermainan)
                ->withErrors(['file' => 'Sesi import sudah kedaluwarsa. Silakan unggah ulang file.']);
        }

        $result = $this->importService->parseAndValidate($papanPermainan, Storage::disk(self::TEMP_DISK)->path($storedPath));
        $updated = $this->importService->commit($papanPermainan, $result['valid']);

        Storage::disk(self::TEMP_DISK)->delete($storedPath);

        $this->notifications->sendToAdmins($this->notifications->payloadImportSuccess('petak', $updated));

        return redirect()->route('admin.management.papan-permainan.petak.index', $papanPermainan)
            ->with('status', "{$updated} petak berhasil diperbarui lewat import.");
    }
}
