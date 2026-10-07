<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::latest()->paginate(25);
        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification)
    {
        $notification->update(['read_at' => now()]);
        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        Notification::whereNull('read_at')->update(['read_at' => now()]);
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
