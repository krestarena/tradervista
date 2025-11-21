<?php

namespace App\Support;

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

    protected static function castValue($value, $default)
    {
        if (is_numeric($value)) {
            return $value + 0; // convert to int or float
        }

        return $value ?? $default;
    }
}
