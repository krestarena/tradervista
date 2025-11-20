<?php

namespace App\Support;

class TradeVistaConfig
{
    protected static array $referralKeyMap = [
        'buyer_pct' => 'tradevista_referral_buyer_pct',
        'seller_pct' => 'tradevista_referral_seller_pct',
        'min_order' => 'tradevista_referral_min_order',
        'voucher_expiry_days' => 'tradevista_referral_voucher_expiry_days',
        'rounding_mode' => 'tradevista_referral_rounding_mode',
    ];

    public static function referral(string $key, $default = null)
    {
        $value = null;
        if (isset(self::$referralKeyMap[$key])) {
            $setting = get_setting(self::$referralKeyMap[$key]);
            if (!is_null($setting) && $setting !== '') {
                $value = $setting;
            }
        }

        if (!is_null($value)) {
            return is_numeric($value) ? (float) $value : $value;
        }

        $config = config('tradevista.referral');
        return $config[$key] ?? $default;
    }

    public static function referralFloat(string $key, float $default = 0): float
    {
        return (float) self::referral($key, $default);
    }

    public static function roundReferralAmount(float $amount): float
    {
        $mode = strtolower((string) self::referral('rounding_mode', 'nearest'));
        return match ($mode) {
            'up', 'ceil' => round(ceil($amount * 100) / 100, 2),
            'down', 'floor' => round(floor($amount * 100) / 100, 2),
            default => round($amount, 2),
        };
    }
}
