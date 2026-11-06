<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoyaltyActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'info',
        public ?string $actionUrl = null,
        public array $meta = []
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Kirim ke email di production atau jika explicitly diperlukan
        if (config('app.env') === 'production' || $this->type === 'success') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'meta' => $this->meta,
        ];
    }

    public function toMail(object $notifiable)
    {
        return (new MailMessage)
            ->subject($this->title)
            ->line($this->message)
            ->when($this->actionUrl, fn ($mail) => $mail->action('View Details', $this->actionUrl))
            ->line('Thank you for being a loyal member!')
            ->line('Khanaan Loyalty Program');
    }
}