<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mark a specific notification as read and redirect.
     */
 public function markAsRead(Request $request)
{
    $user = auth()->user();


    if ($user->unreadNotifications) {
        $user->unreadNotifications->markAsRead();
    }

    return redirect('/my-bookings');
}

    /**
     * Optional: A view to see all past notifications
     */
    public function index()
    {
        $notifications = Auth::user()->notifications()->paginate(10);
        return view('notifications.index', compact('notifications'));
    }
}
