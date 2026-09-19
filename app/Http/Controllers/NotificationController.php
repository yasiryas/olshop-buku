<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()->notifications()
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
            'unread' => $request->user()->unreadNotifications()->count(),
            'data' => $notifications,
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}