<?php

return [
    'voucher_wallet_enabled' => env('TRADEVISTA_VOUCHER_WALLET_ENABLED', true),
    'payment_protection_window_days' => env('TRADEVISTA_PAYMENT_PROTECTION_WINDOW_DAYS', 5),
    'dispute_window_hours' => env('TRADEVISTA_DISPUTE_WINDOW_HOURS', 48),
    'own_dispatch_option_enabled' => env('TRADEVISTA_OWN_DISPATCH_ENABLED', true),
    'own_dispatch_modal_enabled' => env('TRADEVISTA_OWN_DISPATCH_MODAL_ENABLED', true),
    'referral' => [
        'buyer_pct' => env('TRADEVISTA_REFERRAL_BUYER_PCT', 10),
        'seller_pct' => env('TRADEVISTA_REFERRAL_SELLER_PCT', 5),
        'min_order' => env('TRADEVISTA_REFERRAL_MIN_ORDER', 10000),
        'voucher_expiry_days' => env('TRADEVISTA_REFERRAL_VOUCHER_EXPIRY_DAYS', 365),
    ],
];
