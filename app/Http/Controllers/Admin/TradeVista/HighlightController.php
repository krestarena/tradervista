<?php

namespace App\Http\Controllers\Admin\TradeVista;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Support\TradeVistaSettings;
use Illuminate\Http\Request;

class HighlightController extends Controller
{
    public function edit()
    {
        $settings = [
            'highlight_enabled' => TradeVistaSettings::bool('highlight.enabled', false),
            'highlight_start_at' => TradeVistaSettings::get('highlight.start_at'),
            'highlight_end_at' => TradeVistaSettings::get('highlight.end_at'),
            'highlight_vendors' => implode(',', TradeVistaSettings::highlightedVendorIds()),
            'highlight_products' => implode(',', TradeVistaSettings::highlightedProductIds()),
        ];

        return view('backend.tradevista.highlights', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'highlight_enabled' => 'sometimes|boolean',
            'highlight_start_at' => 'nullable|date',
            'highlight_end_at' => 'nullable|date|after_or_equal:highlight_start_at',
            'highlight_vendors' => 'nullable|string',
            'highlight_products' => 'nullable|string',
        ]);

        $this->storeSetting('tradevista_highlight_enabled', $request->boolean('highlight_enabled'));
        $this->storeSetting('tradevista_highlight_start_at', $data['highlight_start_at'] ?? null);
        $this->storeSetting('tradevista_highlight_end_at', $data['highlight_end_at'] ?? null);
        $this->storeSetting('tradevista_highlight_vendors', $this->normalizeIds($data['highlight_vendors'] ?? ''));
        $this->storeSetting('tradevista_highlight_products', $this->normalizeIds($data['highlight_products'] ?? ''));

        flash(translate('TradeVista highlights updated.'))->success();

        return redirect()->route('admin.tradevista.highlights.edit');
    }

    protected function storeSetting(string $type, $value): void
    {
        BusinessSetting::updateOrCreate(
            ['type' => $type],
            ['value' => $value ?? '']
        );
    }

    protected function normalizeIds(string $csv): string
    {
        $list = array_filter(array_map('trim', explode(',', $csv)), static fn ($entry) => $entry !== '');

        $ids = collect($list)
            ->map(static fn ($entry) => (int) preg_replace('/\D/', '', $entry))
            ->filter()
            ->unique()
            ->values()
            ->all();

        return implode(',', $ids);
    }
}
