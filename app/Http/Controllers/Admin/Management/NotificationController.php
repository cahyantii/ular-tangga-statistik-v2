<?php

namespace App\Http\Controllers\Admin\Management;

use App\Exports\NotificationExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * "Notification Center" admin (Tahap 19) - hanya menampilkan notifikasi milik
 * admin yang sedang login (auth()->user()->notifications()), yaitu seluruh
 * notifikasi fan-out admin yang dibuat NotificationService. Player TIDAK
 * pernah muncul di sini karena baris notifikasi player disimpan pada baris
 * User player itu sendiri (kepemilikan tabel notifications, bukan filter
 * kode) - lihat App\Notifications\AppNotification.
 */
class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status', 'semua')->toString();
        $category = $request->string('category', 'semua')->toString();
        $search = $request->string('search')->toString();
        $from = $request->date('from');
        $to = $request->date('to');

        $query = $this->filteredQuery($request->user(), $status, $category, $search, $from, $to);

        $notifications = $query->paginate(15)->withQueryString();

        $base = $request->user()->notifications();

        return view('admin.management.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
            'category' => $category,
            'search' => $search,
            'from' => $request->string('from')->toString(),
            'to' => $request->string('to')->toString(),
            'stats' => [
                'hari_ini' => (clone $base)->whereDate('created_at', today())->count(),
                'minggu_ini' => (clone $base)->where('created_at', '>=', now()->startOfWeek())->count(),
                'bulan_ini' => (clone $base)->where('created_at', '>=', now()->startOfMonth())->count(),
                'belum_dibaca' => (clone $base)->whereNull('read_at')->count(),
            ],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $status = $request->string('status', 'semua')->toString();
        $category = $request->string('category', 'semua')->toString();
        $search = $request->string('search')->toString();
        $from = $request->date('from');
        $to = $request->date('to');

        $notifications = $this->filteredQuery($request->user(), $status, $category, $search, $from, $to)->get();

        return Excel::download(new NotificationExport($notifications), 'notifikasi-'.now()->format('Y-m-d').'.xlsx');
    }

    private function filteredQuery($user, string $status, string $category, string $search, $from, $to)
    {
        return $user->notifications()
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($category !== 'semua', fn ($query) => $query->where('data->category', $category))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('data->title', 'like', "%{$search}%")
                    ->orWhere('data->message', 'like', "%{$search}%");
            }))
            ->when($from, fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->latest();
    }
}
