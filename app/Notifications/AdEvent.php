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
            ->markdown('mail.ad-event', [
                'ad'         => $this->ad->loadMissing(['city', 'category', 'user']),
                'name'       => $notifiable->name,
                'heading'    => $isReport ? 'An ad was reported' : 'An ad needs your review',
                'summary'    => $this->message,
                'url'        => route($isReport ? 'admin.reports.index' : 'admin.ads.index'),
                'buttonText' => $isReport ? 'Review reports' : 'Review pending ads',
            ]);
    }//what is sent to pusher ,and saved to the Database (for the bell icon).down
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
