<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    public function index(Request $request): View
    {
        $notifications = Notification::forUser($request->user())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsReadForUser($notification, $request->user());

        return back()->with('success', __('messages.notification_read'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllAsReadForUser($request->user());

        return back()->with('success', __('messages.notification_all_read'));
    }
}
