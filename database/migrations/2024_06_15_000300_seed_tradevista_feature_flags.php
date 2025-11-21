<?php

use App\Models\BusinessSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $settings = [
            'tradevista_voucher_wallet_enabled' => config('tradevista.voucher_wallet_enabled'),
            'tradevista_payment_protection_window_days' => config('tradevista.payment_protection_window_days'),
            'tradevista_dispute_window_hours' => config('tradevista.dispute_window_hours'),
            'tradevista_otp_expiry_minutes' => config('tradevista.otp_expiry_minutes'),
            'tradevista_otp_daily_limit' => config('tradevista.otp_daily_limit'),
            'tradevista_own_dispatch_option_enabled' => config('tradevista.own_dispatch_option_enabled'),
            'tradevista_own_dispatch_modal_enabled' => config('tradevista.own_dispatch_modal_enabled'),
            'tradevista_own_dispatch_immediate_payout' => config('tradevista.own_dispatch_immediate_payout'),
            'tradevista_payment_protection_badge_enabled' => config('tradevista.payment_protection_badge_enabled'),
            'tradevista_click_and_collect_enabled' => config('tradevista.click_and_collect_enabled'),
            'tradevista_click_and_collect_notification' => config('tradevista.click_and_collect_notification'),
            'tradevista_dispatcher_selection_enabled' => config('tradevista.dispatcher_selection_enabled'),
            'tradevista_voucher_redemption_portal_enabled' => config('tradevista.voucher_redemption_portal_enabled'),
            'tradevista_commission_statement_exports_enabled' => config('tradevista.commission_statement_exports_enabled'),
            'tradevista_audit_logging_enabled' => config('tradevista.audit_logging_enabled'),
            'tradevista_weekly_settlement_enabled' => config('tradevista.weekly_settlement_enabled'),
            'tradevista_seller_promotions_enabled' => config('tradevista.seller_promotions_enabled'),
            'tradevista_seller_promotions_default_window_days' => config('tradevista.seller_promotions_default_window_days'),
            'tradevista_seller_promotions_max_discount_pct' => config('tradevista.seller_promotions_max_discount_pct'),
            'tradevista_highlight_enabled' => config('tradevista.highlight.enabled'),
            'tradevista_highlight_start_at' => config('tradevista.highlight.start_at'),
            'tradevista_highlight_end_at' => config('tradevista.highlight.end_at'),
            'tradevista_highlight_vendors' => config('tradevista.highlight.vendors'),
            'tradevista_highlight_products' => config('tradevista.highlight.products'),
        ];

        foreach ($settings as $type => $value) {
            BusinessSetting::updateOrCreate(
                ['type' => $type],
                ['value' => $value]
            );
        }
    }

    public function down(): void
    {
        BusinessSetting::whereIn('type', [
            'tradevista_voucher_wallet_enabled',
            'tradevista_payment_protection_window_days',
            'tradevista_dispute_window_hours',
            'tradevista_otp_expiry_minutes',
            'tradevista_otp_daily_limit',
            'tradevista_own_dispatch_option_enabled',
            'tradevista_own_dispatch_modal_enabled',
            'tradevista_own_dispatch_immediate_payout',
            'tradevista_payment_protection_badge_enabled',
            'tradevista_click_and_collect_enabled',
            'tradevista_click_and_collect_notification',
            'tradevista_dispatcher_selection_enabled',
            'tradevista_voucher_redemption_portal_enabled',
            'tradevista_commission_statement_exports_enabled',
            'tradevista_audit_logging_enabled',
            'tradevista_weekly_settlement_enabled',
            'tradevista_seller_promotions_enabled',
            'tradevista_seller_promotions_default_window_days',
            'tradevista_seller_promotions_max_discount_pct',
            'tradevista_highlight_enabled',
            'tradevista_highlight_start_at',
            'tradevista_highlight_end_at',
            'tradevista_highlight_vendors',
            'tradevista_highlight_products',
        ])->delete();
    }
};
