<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\TradeVistaSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TradeVistaExpireSellerPromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tradevista:expire-seller-promotions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert expired seller discounts when TradeVista promotions enforcement is enabled';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!TradeVistaSettings::bool('seller_promotions_enabled', false)) {
            $this->info('TradeVista seller promotions enforcement is disabled; skipping.');

            return Command::SUCCESS;
        }

        $now = Carbon::now()->timestamp;
        $expiredCount = 0;

        Product::whereNotNull('discount_end_date')
            ->where('discount_end_date', '<=', $now)
            ->where('discount', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($products) use (&$expiredCount) {
                foreach ($products as $product) {
                    $product->update([
                        'discount' => 0,
                        'discount_type' => null,
                        'discount_start_date' => null,
                        'discount_end_date' => null,
                    ]);

                    $expiredCount++;
                }
            });

        $this->info("Expired seller promotions cleared: {$expiredCount}");

        return Command::SUCCESS;
    }
}
