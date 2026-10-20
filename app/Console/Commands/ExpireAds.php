<?php

namespace App\Console\Commands;

use App\Models\Ad;
use Illuminate\Console\Command;

class ExpireAds extends Command
{
    protected $signature = 'ads:expire';

    protected $description = 'Mark active ads past their expiry date as expired';

    public function handle(): int
    {
        $count = Ad::where('status', Ad::STATUS_ACTIVE)
            ->where('expires_at', '<', now())
            ->update(['status' => Ad::STATUS_EXPIRED]);

        $this->info("Expired {$count} ad(s).");

        return self::SUCCESS;
    }
}
