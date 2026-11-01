<?php

namespace App\Console\Commands;

use App\Models\Ad;
use App\Notifications\AdEvent;
use Illuminate\Console\Command;

class UnprocessedAds extends Command
{
    protected $signature = 'ads:unprocessed';

    protected $description = 'Delete pending ads past two days without processing';

    public function handle() :int
    {
        $ads = Ad::where('status', Ad::STATUS_PENDING)
            ->where('updated_at', '<', now()->subDays(2))
            ->with('user')
            ->get();

        foreach ($ads as $ad) {
            $ad ->forceFill(['status' => Ad::STATUS_DELETED])->save();

            $ad->user->notify(new AdEvent(
                $ad ,'deleted',
                "your ad \"{$ad->title}\" has been deleted because it has not been approved since two days."
            ));

        }

        $this->info("deleted {$ads->count()} pending ads}");

        return self::SUCCESS;

    }



}
