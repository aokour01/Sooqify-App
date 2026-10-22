<?php

namespace App\Notifications;

use App\Models\Ad;
use Illuminate\Notifications\Notification;

class AdEvent extends Notification
{
    /**
     * @param string $type  approved | rejected | expired | report
     */
    public function __construct(
        private Ad $ad,
        private string $type,
        private string $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
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
