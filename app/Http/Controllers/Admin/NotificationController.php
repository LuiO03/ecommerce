<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function redirect(DatabaseNotification $notification, Request $request)
    {
        $user = $request->user();

        if (! $this->belongsToUser($notification, $user)) {
            abort(403);
        }

        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('admin.dashboard');

        return redirect()->to($url);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return back();
    }

    protected function belongsToUser(DatabaseNotification $notification, $user): bool
    {
        return $notification->notifiable_id === $user->getKey()
            && $notification->notifiable_type === get_class($user);
    }
}
