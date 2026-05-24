<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get recent notifications and unread count for the logged-in user.
     */
    public function getNotifications()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'notifications' => []]);
        }

        $unreadCount = $user->unreadNotifications()->count();
        $notifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'title' => $notif->data['title'] ?? 'Notifikasi',
                    'message' => $notif->data['message'] ?? '',
                    'url' => $notif->data['url'] ?? '#',
                    'type' => $notif->data['type'] ?? 'info',
                    'read' => !is_null($notif->read_at),
                    'time' => $notif->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if ($user) {
            $notification = $user->notifications()->where('id', $id)->first();
            if ($notification) {
                $notification->markAsRead();
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
    }
}
