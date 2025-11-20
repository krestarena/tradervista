<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'otp_expires_at')) {
                $table->timestamp('otp_expires_at')->nullable()->after('verification_code');
            }
            if (!Schema::hasColumn('users', 'otp_last_sent_at')) {
                $table->timestamp('otp_last_sent_at')->nullable()->after('otp_expires_at');
            }
            if (!Schema::hasColumn('users', 'otp_daily_count')) {
                $table->unsignedInteger('otp_daily_count')->default(0)->after('otp_last_sent_at');
            }
            if (!Schema::hasColumn('users', 'otp_daily_counted_at')) {
                $table->date('otp_daily_counted_at')->nullable()->after('otp_daily_count');
            }
            if (!Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code')->nullable()->unique()->after('remember_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'otp_expires_at')) {
                $table->dropColumn('otp_expires_at');
            }
            if (Schema::hasColumn('users', 'otp_last_sent_at')) {
                $table->dropColumn('otp_last_sent_at');
            }
            if (Schema::hasColumn('users', 'otp_daily_count')) {
                $table->dropColumn('otp_daily_count');
            }
            if (Schema::hasColumn('users', 'otp_daily_counted_at')) {
                $table->dropColumn('otp_daily_counted_at');
            }
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropUnique('users_referral_code_unique');
                $table->dropColumn('referral_code');
            }
        });
    }
};
