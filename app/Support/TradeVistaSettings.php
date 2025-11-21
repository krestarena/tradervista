<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Arr;

class TradeVistaSettings
{
    public static function get(string $key, $default = null)
    {
        $settingKey = 'tradevista_' . str_replace('.', '_', $key);
        $setting = get_setting($settingKey);

        if (!is_null($setting) && $setting !== '') {
            return self::castValue($setting, $default);
        }

        return Arr::get(config('tradevista'), $key, $default);
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = self::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        $normalized = is_string($value) ? strtolower($value) : $value;
        if (in_array($normalized, ['1', 1, 'true', true, 'on', 'yes'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 0, 'false', false, 'off', 'no'], true)) {
            return false;
        }

        return (bool) $value;
    }

    public static function int(string $key, int $default = 0): int
    {
        return (int) self::get($key, $default);
    }

    public static function float(string $key, float $default = 0): float
    {
        return (float) self::get($key, $default);
    }

    public static function list(string $key, string $delimiter = ','): array
    {
        $value = self::get($key, '');

        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), static fn ($entry) => $entry !== ''));
        }

        if (!is_string($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode($delimiter, $value)), static fn ($entry) => $entry !== ''));
    }

    public static function highlightWindowActive(): bool
    {
        if (!self::bool('highlight.enabled', false)) {
            return false;
        }

        $start = self::get('highlight.start_at');
        $end = self::get('highlight.end_at');
        $now = Carbon::now();

        if ($start && $now->lt(Carbon::parse($start))) {
            return false;
        }

        if ($end && $now->gt(Carbon::parse($end))) {
            return false;
        }

        return true;
    }

    public static function highlightedProductIds(): array
    {
        return self::normalizeIdList(self::list('highlight.products'));
    }

    public static function highlightedVendorIds(): array
    {
        return self::normalizeIdList(self::list('highlight.vendors'));
    }

    public static function isProductHighlighted($product): bool
    {
        if (!$product) {
            return false;
        }

        if (!self::highlightWindowActive()) {
            return false;
        }

        $highlightedProductIds = self::highlightedProductIds();
        $highlightedVendorIds = self::highlightedVendorIds();

        $productId = (int) data_get($product, 'id');
        $vendorId = (int) data_get($product, 'user_id');

        return in_array($productId, $highlightedProductIds, true) || in_array($vendorId, $highlightedVendorIds, true);
    }

    protected static function castValue($value, $default)
    {
        if (is_numeric($value)) {
            return $value + 0; // convert to int or float
        }

        return $value ?? $default;
    }

    protected static function normalizeIdList(array $values): array
    {
        return collect($values)
            ->map(static function ($value) {
                if (is_numeric($value)) {
                    return (int) $value;
                }

                return is_string($value) ? (int) preg_replace('/\D/', '', $value) : null;
            })
            ->filter(static fn ($value) => $value !== null)
            ->unique()
            ->values()
            ->all();
    }
}
