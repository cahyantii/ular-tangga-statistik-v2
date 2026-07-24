<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Endpoint notifikasi BERSAMA (dipakai admin maupun player) - keduanya
 * beroperasi murni pada auth()->user()->notifications(), sehingga admin
 * tidak pernah bisa melihat/menandai notifikasi milik player atau sebaliknya
 * (dijamin oleh kepemilikan baris, bukan oleh kode di controller ini).
 *
 * markRead()/markAllRead()/destroy() dipakai dua cara: fetch() dari dropdown
 * bell (JS, expects JSON) dan form HTML biasa di halaman Notification Center
 * admin (expects redirect kembali) - dibedakan lewat wantsJson().
 */
class NotificationController extends Controller
{
    private const RECENT_LIMIT = 10;

    public function recent(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()->latest()->limit(self::RECENT_LIMIT)->get();

        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
            'notifications' => $notifications->map(fn ($notification) => $this->formatNotification($notification)),
        ]);
    }

    public function markRead(Request $request, string $notification): JsonResponse|RedirectResponse
    {
        $model = $request->user()->notifications()->findOrFail($notification);
        $model->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }

    public function destroy(Request $request, string $notification): JsonResponse|RedirectResponse
    {
        $model = $request->user()->notifications()->findOrFail($notification);
        $model->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back();
    }

    private function formatNotification($notification): array
    {
        return [
            'id' => $notification->id,
            'title' => $notification->data['title'] ?? '',
            'message' => $notification->data['message'] ?? '',
            'icon' => $notification->data['icon'] ?? 'bell',
            'color' => $notification->data['color'] ?? 'blue',
            'category' => $notification->data['category'] ?? 'system',
            'url' => $notification->data['url'] ?? null,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at->toIso8601String(),
            'created_at_human' => $notification->created_at->diffForHumans(),
        ];
    }
}
