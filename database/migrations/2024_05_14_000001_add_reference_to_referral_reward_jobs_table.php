<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('referral_reward_jobs')) {
            Schema::table('referral_reward_jobs', function (Blueprint $table) {
                if (!Schema::hasColumn('referral_reward_jobs', 'reference')) {
                    $table->string('reference')->nullable()->index()->after('id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('referral_reward_jobs') && Schema::hasColumn('referral_reward_jobs', 'reference')) {
            Schema::table('referral_reward_jobs', function (Blueprint $table) {
                $table->dropColumn('reference');
            });
        }
    }
};
