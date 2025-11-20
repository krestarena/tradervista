<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('voucher_wallets')) {
            Schema::create('voucher_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->decimal('balance', 20, 2)->default(0);
                $table->decimal('locked_balance', 20, 2)->default(0);
                $table->date('default_expiry')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('voucher_wallet_ledgers')) {
            Schema::create('voucher_wallet_ledgers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('voucher_wallet_id');
                $table->string('type', 20);
                $table->decimal('amount', 20, 2);
                $table->decimal('balance_after', 20, 2)->default(0);
                $table->string('reference')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('combined_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('combined_orders', 'voucher_deduction')) {
                $table->decimal('voucher_deduction', 20, 2)->default(0);
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'dispatch_mode')) {
                $table->string('dispatch_mode')->default('platform_dispatch');
            }
            if (!Schema::hasColumn('orders', 'payment_protection_status')) {
                $table->string('payment_protection_status')->default('not_applicable');
            }
            if (!Schema::hasColumn('orders', 'payment_protection_released_at')) {
                $table->timestamp('payment_protection_released_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_protection_hold_expires_at')) {
                $table->timestamp('payment_protection_hold_expires_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'pickup_ready_at')) {
                $table->timestamp('pickup_ready_at')->nullable();
            }
            if (!Schema::hasColumn('orders', 'pickup_window_end')) {
                $table->timestamp('pickup_window_end')->nullable();
            }
        });

        if (!Schema::hasTable('vendor_kyc_submissions')) {
            Schema::create('vendor_kyc_submissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->json('documents')->nullable();
                $table->string('status')->default('pending');
                $table->json('rejection_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('referral_reward_jobs')) {
            Schema::create('referral_reward_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('status')->default('pending');
                $table->json('payload');
                $table->timestamp('run_at')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('disputes')) {
            Schema::create('disputes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('order_id');
                $table->string('status')->default('open');
                $table->json('evidence')->nullable();
                $table->string('resolution')->nullable();
                $table->unsignedBigInteger('resolved_by')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('disputes');
        Schema::dropIfExists('referral_reward_jobs');
        Schema::dropIfExists('vendor_kyc_submissions');
        Schema::dropIfExists('voucher_wallet_ledgers');
        Schema::dropIfExists('voucher_wallets');

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'dispatch_mode',
                'payment_protection_status',
                'payment_protection_released_at',
                'payment_protection_hold_expires_at',
                'pickup_ready_at',
                'pickup_window_end',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('combined_orders', 'voucher_deduction')) {
            Schema::table('combined_orders', function (Blueprint $table) {
                $table->dropColumn('voucher_deduction');
            });
        }
    }
};
