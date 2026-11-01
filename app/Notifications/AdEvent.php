<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdEvent extends Notification
{
    use Queueable;
    /**
     * @param string $type  approved | rejected | expired | report
     */
    public function __construct(
        public Ad $ad,
        public string $type,
        public string $message,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'broadcast'];

        if (in_array($this->type, ['pending', 'report'], true) && $notifiable->hasRole('admin')) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /** The live payload's "type". Without this it would be the class name. */
    public function broadcastType(): string
    {
        return $this->type;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isReport = $this->type === 'report';

        return (new MailMessage)
            ->subject($isReport ? 'New report on an ad' : 'New ad waiting for approval')
            ->greeting("Hello {$notifiable->name}")
            ->line($this->message)
            ->line('Ad Title : ' . $this->ad->title)
            ->line('Ad Slug : ' . $this->ad->slug)
            ->action($isReport ? 'View reports' : 'View waiting ads', route($isReport ? 'admin.reports.index' : 'admin.ads.index'))
            ->line('Thank you for supervising us!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => $this->type,
            'message'  => $this->message,
            'ad_slug'  => $this->ad->slug,
            'ad_title' => $this->ad->title,
        ];
    }
}
