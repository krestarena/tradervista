<?php

namespace App\Console;

use App\Console\Commands\ProcessReferralRewards;
use App\Console\Commands\ReleasePaymentProtectionHolds;
use App\Console\Commands\TradeVistaExpireSellerPromotions;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        ReleasePaymentProtectionHolds::class,
        ProcessReferralRewards::class,
        TradeVistaExpireSellerPromotions::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tradevista:release-payment-protection')->hourly();
        $schedule->command('tradevista:process-referrals')->hourly();
        $schedule->command('tradevista:expire-seller-promotions')->dailyAt('00:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
