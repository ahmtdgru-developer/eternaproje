<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostCommentApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Comment $comment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $authorName = trim(($this->comment->user?->name ?? '') . ' ' . ($this->comment->user?->surname ?? ''));

        return (new MailMessage)
            ->subject('Yorumunuz Onaylandı')
            ->greeting('Merhaba,')
            ->line("{$this->comment->post?->title} başlıklı yazınıza yeni bir yorum onaylandı.")
            ->line("Yorumu yapan: {$authorName}")
            ->line('Yorum: ' . $this->comment->content)
            ->action('Yazıyı Görüntüle', url('/posts/' . $this->comment->post_id))
            ->line('Bilgilendirme amacıyla gönderilmiştir.');
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->payload());
    }

    private function payload(): array
    {
        return [
            'post_id' => $this->comment->post_id,
            'post_title' => $this->comment->post?->title,
            'comment_id' => $this->comment->id,
            'comment_content' => $this->comment->content,
            'comment_author' => [
                'id' => $this->comment->user?->id,
                'name' => $this->comment->user?->name,
                'surname' => $this->comment->user?->surname,
            ],
            'message' => 'Yazınıza yapılan bir yorum onaylandı.',
        ];
    }
}