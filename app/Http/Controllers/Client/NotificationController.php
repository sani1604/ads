<?php
// app/Http/Controllers/Client/NotificationController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'onboarding']);
    }

    /**
     * List all notifications
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        $query = $user->notifications();

        if ($request->get('unread_only')) {
            $query->unread();
        }

        $notifications = $query->latest()->paginate(20);

        return view('client.notifications.index', compact('notifications'));
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(CustomNotification $notification)
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        
        NotificationService::markAllAsRead($user);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Get unread count (AJAX)
     */
    public function unreadCount()
    {
        $count = auth()->user()->getUnreadNotificationsCount();

        return response()->json(['count' => $count]);
    }

    /**
     * Get recent notifications (AJAX for dropdown)
     */
    public function recent()
    {
        $notifications = auth()->user()
            ->notifications()
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => auth()->user()->getUnreadNotificationsCount(),
        ]);
    }
}