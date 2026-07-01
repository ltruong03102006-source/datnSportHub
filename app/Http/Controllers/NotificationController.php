<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $this->notificationsFor($request)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationsFor($request)->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->back();
    }

    public function unreadCount(Request $request)
    {
        if (! $this->wantsJsonResponse($request)) {
            return $this->redirectByRole($request);
        }

        $count = $this->notificationsFor($request)->where('is_read', false)->count();
        return response()->json(['count' => $count]);
    }

    public function latest(Request $request)
    {
        if (! $this->wantsJsonResponse($request)) {
            return $this->redirectByRole($request);
        }

        $notifications = $this->notificationsFor($request)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id','title','content','link','is_read','created_at']);

        return response()->json($notifications);
    }

    private function notificationsFor(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id);
        $context = $request->query('context');

        if ($context === 'owner') {
            return $query->where('type', 'like', 'owner\_%');
        }

        if ($context === 'customer') {
            return $query->where(function ($inner) {
                $inner->whereNull('type')
                    ->orWhere('type', 'not like', 'owner\_%');
            });
        }

        return $query;
    }

    private function wantsJsonResponse(Request $request): bool
    {
        return $request->expectsJson() || $request->ajax();
    }

    private function redirectByRole(Request $request)
    {
        return match ($request->user()?->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'owner' => redirect()->route('owner.dashboard'),
            default => redirect()->route('home'),
        };
    }
}
