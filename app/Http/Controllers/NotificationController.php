<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationFilterRequest;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(NotificationFilterRequest $request): View
    {
        $view = $request->viewMode();
        $baseQuery = Notification::forUser($request->user());
        $unreadCount = (clone $baseQuery)->unread()->count();

        $notifications = $baseQuery
            ->when($view === NotificationFilterRequest::VIEW_UNREAD, fn ($query) => $query->unread())
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', compact('notifications', 'unreadCount', 'view'));
    }

    public function markAsRead(Request $request, Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsReadForUser($notification, $request->user());

        return back()->with('success', __('messages.notification_read'));
    }

    public function markAsUnread(Request $request, Notification $notification): RedirectResponse
    {
        $this->notificationService->markAsUnreadForUser($notification, $request->user());

        return back()->with('success', __('messages.notification_unread'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllAsReadForUser($request->user());

        return back()->with('success', __('messages.notification_all_read'));
    }
}
