<?php

namespace App\Http\Controllers;

use App\Notifications\ArticleCreatedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->notifications();

        if ($user->hasRole('penulis')) {
            $query->where('type', ArticleCreatedNotification::class);
        }

        $notifications = $query
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'message' => data_get($n->data, 'message'),
                'url' => data_get($n->data, 'url'),
                'order_id' => data_get($n->data, 'order_id'),
                'type' => data_get($n->data, 'type'),
                'read' => !is_null($n->read_at),
                'time' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'unread' => $user->unreadNotifications()
                ->when($user->hasRole('penulis'), fn ($q) => $q->where('type', ArticleCreatedNotification::class))
                ->count(),
            'data' => $notifications,
        ]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}