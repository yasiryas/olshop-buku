<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ArticleCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Article $article) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Artikel baru: '.Str::limit($this->article->title, 60),
            'url' => route('front.article.details', $this->article->slug),
            'type' => 'article_created',
        ];
    }
}