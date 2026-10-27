<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;
use App\Notifications\AdEvent;

class ExpireAds extends Command
{
    protected $signature = 'ads:expire';

    protected $description = 'Mark active ads past their expiry date as expired';

    public function handle(): int
    {
        $ads = Ad::where('status', Ad::STATUS_ACTIVE)
            ->where('expires_at', '<', now())
            ->with('user')
            ->get();

        foreach ($ads as $ad) {
            $ad->forceFill(['status' => Ad::STATUS_EXPIRED])->save();

            $ad->user->notify(new AdEvent(
                $ad, 'expired',
                "Your ad \"{$ad->title}\" has expired. You can renew it from My ads."
            ));
        }

        $this->info("Expired {$ads->count()} ad(s).");

        return self::SUCCESS;
    }
}
