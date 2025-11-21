<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('commission_plans')) {
            Schema::create('commission_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('percentage', 5, 2);
                $table->text('notes')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('commission_plan_category')) {
            Schema::create('commission_plan_category', function (Blueprint $table) {
                $table->unsignedBigInteger('commission_plan_id');
                $table->unsignedBigInteger('category_id');
                $table->primary(['commission_plan_id', 'category_id']);
            });
        }

        Schema::table('commission_histories', function (Blueprint $table) {
            if (!Schema::hasColumn('commission_histories', 'commission_plan_id')) {
                $table->unsignedBigInteger('commission_plan_id')->nullable()->after('order_id');
            }
        });

        if (!Schema::hasTable('trade_vista_cards')) {
            Schema::create('trade_vista_cards', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->decimal('balance', 20, 2)->default(0);
                $table->decimal('locked_balance', 20, 2)->default(0);
                $table->string('status')->default('active');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('last_redeemed_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('trade_vista_card_transactions')) {
            Schema::create('trade_vista_card_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('trade_vista_card_id');
                $table->unsignedBigInteger('merchant_id');
                $table->decimal('amount', 20, 2);
                $table->string('type', 20);
                $table->string('reference')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_vista_card_transactions');
        Schema::dropIfExists('trade_vista_cards');

        Schema::table('commission_histories', function (Blueprint $table) {
            if (Schema::hasColumn('commission_histories', 'commission_plan_id')) {
                $table->dropColumn('commission_plan_id');
            }
        });

        Schema::dropIfExists('commission_plan_category');
        Schema::dropIfExists('commission_plans');
    }
};
