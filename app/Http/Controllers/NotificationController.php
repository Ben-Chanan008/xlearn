<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Marks the notification read and goes to the page it points at
     */
    public function open(Request $request, string $id)
    {
        // Through the user's own notifications, so nobody can open someone else's
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function readAll(Request $request)
    {
        // One UPDATE for all of them, rather than loading each and saving it separately
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}