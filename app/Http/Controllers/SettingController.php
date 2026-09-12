<?php

namespace App\Http\Controllers;

use App\Support\StoreSettings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => StoreSettings::all(),
            'agenWebCities' => StoreSettings::agenWebCityList(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'wa_contact' => 'required|string|max:20',
            'agenweb_api_key' => 'nullable|string|max:255',
            'agenweb_origin_city_id' => 'nullable|string|max:10',
            'low_stock_threshold' => 'required|integer|min:0',
            'shipping_methods' => 'required|array|min:1',
            'shipping_methods.*.courier' => 'required|string|max:100',
            'shipping_methods.*.cost' => 'required|numeric|min:0',
            'shipping_methods.*.eta' => 'required|string|max:50',
            'shipping_zones' => 'nullable|array',
            'shipping_zones.*.city' => 'required|string|max:255',
            'shipping_zones.*.costs' => 'nullable|array',
            'shipping_zones.*.costs.*' => 'nullable|numeric|min:0',
            'payment_methods' => 'required|array|min:1',
            'payment_methods.*.name' => 'required|string|max:100',
            'payment_methods.*.acc_number' => 'required|string|max:50',
            'payment_methods.*.acc_name' => 'required|string|max:100',
            'payment_methods.*.active' => 'sometimes|boolean',
        ]);

        $shippingMethods = collect($validated['shipping_methods'])
            ->map(fn (array $row, int $index) => [
                'code' => $row['code'] ?? 'courier-' . ($index + 1),
                'courier' => $row['courier'],
                'cost' => (int) $row['cost'],
                'eta' => $row['eta'],
            ])
            ->values()
            ->all();

        $paymentMethods = collect($validated['payment_methods'])
            ->map(fn (array $row, int $index) => [
                'code' => $row['code'] ?? 'method-' . ($index + 1),
                'name' => $row['name'],
                'acc_number' => $row['acc_number'],
                'acc_name' => $row['acc_name'],
                'active' => (bool) ($row['active'] ?? true),
            ])
            ->values()
            ->all();

        $shippingZones = collect($validated['shipping_zones'] ?? [])
            ->map(fn (array $row) => [
                'city' => $row['city'],
                'costs' => collect($row['costs'] ?? [])
                    ->map(fn ($cost) => (int) ($cost ?? 0))
                    ->all(),
            ])
            ->values()
            ->all();

        \App\Models\Setting::updateOrCreate(['key' => 'wa_contact'], ['value' => $validated['wa_contact']]);
        \App\Models\Setting::updateOrCreate(['key' => 'agenweb_api_key'], ['value' => $validated['agenweb_api_key'] ?? '']);
        \App\Models\Setting::updateOrCreate(['key' => 'agenweb_origin_city_id'], ['value' => $validated['agenweb_origin_city_id'] ?? '']);
        \App\Models\Setting::updateOrCreate(['key' => 'low_stock_threshold'], ['value' => $validated['low_stock_threshold']]);
        \App\Models\Setting::updateOrCreate(['key' => 'shipping_methods'], ['value' => $shippingMethods]);
        \App\Models\Setting::updateOrCreate(['key' => 'shipping_zones'], ['value' => $shippingZones]);
        \App\Models\Setting::updateOrCreate(['key' => 'payment_methods'], ['value' => $paymentMethods]);

        StoreSettings::invalidateCache();

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}