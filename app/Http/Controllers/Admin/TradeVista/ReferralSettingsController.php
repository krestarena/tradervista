<?php

namespace App\Http\Controllers\Admin\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\ReferralSettingAudit;
use App\Support\TradeVistaConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralSettingsController extends Controller
{
    protected array $fieldMap = [
        'buyer_pct' => 'tradevista_referral_buyer_pct',
        'seller_pct' => 'tradevista_referral_seller_pct',
        'min_order' => 'tradevista_referral_min_order',
        'voucher_expiry_days' => 'tradevista_referral_voucher_expiry_days',
        'rounding_mode' => 'tradevista_referral_rounding_mode',
    ];

    public function edit()
    {
        $settings = $this->currentSettings();
        $audits = ReferralSettingAudit::with('admin')->latest()->limit(10)->get();

        return view('backend.tradevista.referral_settings', compact('settings', 'audits'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'buyer_pct' => 'required|numeric|min:0|max:100',
            'seller_pct' => 'required|numeric|min:0|max:100',
            'min_order' => 'required|numeric|min:0',
            'voucher_expiry_days' => 'required|integer|min:30|max:1095',
            'rounding_mode' => 'required|in:nearest,up,down',
            'confirm_changes' => 'accepted',
        ], [
            'confirm_changes.accepted' => translate('Please confirm you understand these changes apply to future transactions.'),
        ]);

        $old = $this->currentSettings();
        $payload = collect($data)->only(array_keys($this->fieldMap))->toArray();
        $this->persistSettings($payload);

        ReferralSettingAudit::create([
            'admin_id' => Auth::id(),
            'old_values' => $old,
            'new_values' => $this->currentSettings(),
            'ip_address' => $request->ip(),
        ]);

        flash(translate('Referral settings updated successfully.'))->success();

        return redirect()->route('admin.tradevista.referral-settings.edit');
    }

    protected function currentSettings(): array
    {
        return [
            'buyer_pct' => TradeVistaConfig::referralFloat('buyer_pct', 10),
            'seller_pct' => TradeVistaConfig::referralFloat('seller_pct', 5),
            'min_order' => TradeVistaConfig::referralFloat('min_order', 10000),
            'voucher_expiry_days' => TradeVistaConfig::referralFloat('voucher_expiry_days', 365),
            'rounding_mode' => TradeVistaConfig::referral('rounding_mode', 'nearest'),
        ];
    }

    protected function persistSettings(array $payload): void
    {
        foreach ($payload as $key => $value) {
            if (!isset($this->fieldMap[$key])) {
                continue;
            }

            BusinessSetting::updateOrCreate([
                'type' => $this->fieldMap[$key],
            ], [
                'value' => $value,
            ]);
        }
    }
}
