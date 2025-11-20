<?php

return [
    // Core protections that shipped with the baseline experience remain enabled by default.
    'voucher_wallet_enabled' => env('TRADEVISTA_VOUCHER_WALLET_ENABLED', true),
    'payment_protection_window_days' => env('TRADEVISTA_PAYMENT_PROTECTION_WINDOW_DAYS', 5),
    'dispute_window_hours' => env('TRADEVISTA_DISPUTE_WINDOW_HOURS', 48),
    'otp_expiry_minutes' => env('TRADEVISTA_OTP_EXPIRY_MINUTES', 10),
    'otp_daily_limit' => env('TRADEVISTA_OTP_DAILY_LIMIT', 5),

    // Optional behaviours default to disabled so operators can opt-in per the TradeVista plan.
    'own_dispatch_option_enabled' => env('TRADEVISTA_OWN_DISPATCH_ENABLED', false),
    'own_dispatch_modal_enabled' => env('TRADEVISTA_OWN_DISPATCH_MODAL_ENABLED', false),
    'own_dispatch_immediate_payout' => env('TRADEVISTA_OWN_DISPATCH_IMMEDIATE_PAYOUT', false),
    'payment_protection_badge_enabled' => env('TRADEVISTA_PAYMENT_PROTECTION_BADGE_ENABLED', false),
    'click_and_collect_enabled' => env('TRADEVISTA_CLICK_AND_COLLECT_ENABLED', false),
    'click_and_collect_notification' => env('TRADEVISTA_CLICK_AND_COLLECT_NOTIFICATION', false),
    'dispatcher_selection_enabled' => env('TRADEVISTA_DISPATCHER_SELECTION_ENABLED', false),
    'voucher_redemption_portal_enabled' => env('TRADEVISTA_VOUCHER_REDEMPTION_PORTAL_ENABLED', false),
    'commission_statement_exports_enabled' => env('TRADEVISTA_COMMISSION_STATEMENT_EXPORTS_ENABLED', false),
    'audit_logging_enabled' => env('TRADEVISTA_AUDIT_LOGGING_ENABLED', false),
    'weekly_settlement_enabled' => env('TRADEVISTA_WEEKLY_SETTLEMENT_ENABLED', true),

    'referral' => [
        'buyer_pct' => env('TRADEVISTA_REFERRAL_BUYER_PCT', 10),
        'seller_pct' => env('TRADEVISTA_REFERRAL_SELLER_PCT', 5),
        'min_order' => env('TRADEVISTA_REFERRAL_MIN_ORDER', 10000),
        'voucher_expiry_days' => env('TRADEVISTA_REFERRAL_VOUCHER_EXPIRY_DAYS', 365),
        'unlock_after_payment_protection_days' => env('TRADEVISTA_REFERRAL_UNLOCK_AFTER_DAYS', 5),
        'enable_buyer_rewards' => env('TRADEVISTA_ENABLE_BUYER_REFERRAL_REWARDS', false),
        'enable_seller_rewards' => env('TRADEVISTA_ENABLE_SELLER_REFERRAL_REWARDS', false),
    ],
    'kyc' => [
        'vendor_required' => env('TRADEVISTA_KYC_VENDOR_REQUIRED', false),
        'dispatcher_required' => env('TRADEVISTA_KYC_DISPATCHER_REQUIRED', false),
        'buyer_verification_required' => env('TRADEVISTA_KYC_BUYER_VERIFICATION_REQUIRED', false),
        'admin_approval_required' => env('TRADEVISTA_KYC_ADMIN_APPROVAL_REQUIRED', false),
    ],
    'commission' => [
        'category_matrix_enabled' => env('TRADEVISTA_COMMISSION_CATEGORY_MATRIX_ENABLED', false),
        'plan_modifiers_enabled' => env('TRADEVISTA_COMMISSION_PLAN_MODIFIERS_ENABLED', false),
        'promo_windows_enabled' => env('TRADEVISTA_COMMISSION_PROMO_WINDOWS_ENABLED', false),
    ],
    'vouchers' => [
        'checkout_usage_enabled' => env('TRADEVISTA_VOUCHER_CHECKOUT_USAGE_ENABLED', false),
        'voucher_history_notifications' => env('TRADEVISTA_VOUCHER_HISTORY_NOTIFICATIONS', false),
        'merchant_portal_statements' => env('TRADEVISTA_VOUCHER_MERCHANT_STATEMENTS', false),
    ],
    'disputes' => [
        'evidence_upload_enabled' => env('TRADEVISTA_DISPUTE_EVIDENCE_UPLOAD_ENABLED', false),
        'timeline_notes_enabled' => env('TRADEVISTA_DISPUTE_TIMELINE_NOTES_ENABLED', false),
    ],
];
