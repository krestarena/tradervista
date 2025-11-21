<?php

namespace App\Http\Controllers\Admin\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Support\TradeVistaSettings;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    protected array $fields = [
        'voucher_wallet_enabled' => ['type' => 'boolean'],
        'payment_protection_window_days' => ['type' => 'integer', 'rules' => 'required|integer|min:1|max:30'],
        'dispute_window_hours' => ['type' => 'integer', 'rules' => 'required|integer|min:12|max:168'],
        'otp_expiry_minutes' => ['type' => 'integer', 'rules' => 'required|integer|min:2|max:30'],
        'otp_daily_limit' => ['type' => 'integer', 'rules' => 'required|integer|min:1|max:20'],
        'own_dispatch_option_enabled' => ['type' => 'boolean'],
        'own_dispatch_modal_enabled' => ['type' => 'boolean'],
        'own_dispatch_immediate_payout' => ['type' => 'boolean'],
        'payment_protection_badge_enabled' => ['type' => 'boolean'],
        'click_and_collect_enabled' => ['type' => 'boolean'],
        'click_and_collect_notification' => ['type' => 'boolean'],
        'dispatcher_selection_enabled' => ['type' => 'boolean'],
        'voucher_redemption_portal_enabled' => ['type' => 'boolean'],
        'commission_statement_exports_enabled' => ['type' => 'boolean'],
        'audit_logging_enabled' => ['type' => 'boolean'],
        'weekly_settlement_enabled' => ['type' => 'boolean'],
    ];

    public function edit()
    {
        $settings = collect($this->fields)->mapWithKeys(function ($definition, $key) {
            $default = $definition['type'] === 'boolean' ? false : null;
            $value = match ($definition['type']) {
                'integer' => TradeVistaSettings::int($key, $default ?? 0),
                default => TradeVistaSettings::bool($key, (bool) $default),
            };

            return [$key => $value];
        })->toArray();

        return view('backend.tradevista.feature_flags', compact('settings'));
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach ($this->fields as $key => $definition) {
            $rules[$key] = $definition['rules'] ?? 'sometimes|boolean';
        }

        $data = $request->validate($rules);

        foreach ($this->fields as $key => $definition) {
            $settingKey = 'tradevista_' . str_replace('.', '_', $key);
            $value = $data[$key] ?? ($definition['type'] === 'boolean' ? 0 : null);

            BusinessSetting::updateOrCreate(
                ['type' => $settingKey],
                ['value' => $definition['type'] === 'boolean' ? (int) (bool) $value : $value]
            );
        }

        flash(translate('TradeVista feature flags updated.'))->success();

        return redirect()->route('admin.tradevista.feature-flags.edit');
    }
}
