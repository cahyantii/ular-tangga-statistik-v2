<?php

namespace App\Http\Controllers\Admin\Management;

use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'semua')->toString();

        $feedback = Feedback::query()
            ->with('user')
            ->when($filter !== 'semua', fn ($query) => $query->where('status', $filter))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.management.feedback.index', [
            'feedback' => $feedback,
            'filter' => $filter,
            'stats' => [
                'total' => Feedback::query()->count(),
                'baru' => Feedback::query()->where('status', FeedbackStatus::Baru)->count(),
                'dibaca' => Feedback::query()->where('status', FeedbackStatus::Dibaca)->count(),
                'selesai' => Feedback::query()->where('status', FeedbackStatus::Selesai)->count(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Feedback $feedback): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:baru,dibaca,selesai'],
        ]);

        $feedback->update(['status' => $request->string('status')->toString()]);

        return redirect()->route('admin.management.feedback.index')->with('status', 'Status masukan diperbarui.');
    }

    public function destroy(Feedback $feedback): RedirectResponse
    {
        $feedback->delete();

        return redirect()->route('admin.management.feedback.index')->with('status', 'Masukan dihapus.');
    }
}
