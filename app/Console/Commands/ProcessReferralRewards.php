<?php

namespace App\Console\Commands;

use App\Services\TradeVista\ReferralRewardService;
use Illuminate\Console\Command;

class ProcessReferralRewards extends Command
{
    protected $signature = 'tradevista:process-referrals {--limit=100 : Maximum rewards to process in a single run}';

    protected $description = 'Credit voucher rewards for due TradeVista referral jobs.';

    public function handle(ReferralRewardService $service): int
    {
        $limit = (int) $this->option('limit');
        $count = $service->processPendingJobs(max($limit, 1));
        $this->info("Processed {$count} referral reward job(s).");

        return self::SUCCESS;
    }
}
