<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdminDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public ?string $body = null,
        public ?string $url = null,
        public ?string $icon = null,
        public ?string $level = null,
    ) {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for storage in database.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'icon' => $this->icon ?? 'ri-notification-3-line',
            'level' => $this->level ?? 'info',
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
