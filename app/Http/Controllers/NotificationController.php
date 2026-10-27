<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    /** Marks one notification as read, then sends the user to the related page. */
    public function open(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        $data = $notification->data;

        $url = match ($data['type'] ?? null) {
            'rejected' => route('my-ads.edit', $data['ad_slug']),
            'report'   => route('admin.reports.index'),
            'pending'  => route('admin.ads.index'),
            default    => route('ads.show', $data['ad_slug']),
        };

        return redirect($url);
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }
}
