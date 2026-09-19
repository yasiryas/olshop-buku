<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ProductTransaction;
use App\Models\User;
use App\Notifications\ArticleCreatedNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ProofUploadedNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Seeder;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $admins = User::role(['owner', 'admin'])->get();

        if ($admins->isEmpty()) {
            return;
        }

        DB::table('notifications')->truncate();

        foreach (ProductTransaction::orderBy('created_at')->get() as $transaction) {
            $this->notifyAdmins($admins, $transaction);
            $this->notifyBuyer($transaction);
        }

        $this->notifyPenulis();
    }

    private function notifyPenulis(): void
    {
        $penulis = User::role('penulis')->get();

        if ($penulis->isEmpty()) {
            return;
        }

        foreach (Article::where('is_published', true)->get() as $article) {
            $createdAt = $this->clampToNow($article->published_at ?? $article->created_at);

            foreach ($penulis as $writer) {
                $this->create($writer, new ArticleCreatedNotification($article), $createdAt);
            }
        }
    }

    private function notifyAdmins(EloquentCollection $admins, ProductTransaction $transaction): void
    {
        $createdAt = $this->clampToNow($transaction->created_at);

        foreach ($admins as $admin) {
            $this->create($admin, new OrderCreatedNotification($transaction), $createdAt);

            if ($transaction->isPaid()) {
                $this->create($admin, new ProofUploadedNotification($transaction), $createdAt);
            }
        }
    }

    private function notifyBuyer(ProductTransaction $transaction): void
    {
        if (! $transaction->user) {
            return;
        }

        $this->create(
            $transaction->user,
            new OrderStatusChangedNotification($transaction),
            $this->clampToNow($transaction->updated_at)
        );
    }

    private function create(User $user, Notification $notification, Carbon $createdAt): void
    {
        $now = now();
        $readAt = $createdAt->lt($now->copy()->subDay())
            ? $createdAt->copy()->addMinutes(15)->min($now)
            : null;

        $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => get_class($notification),
            'data' => $notification->toArray($user),
            'read_at' => $readAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function clampToNow(Carbon $date): Carbon
    {
        return $date->min(now());
    }
}