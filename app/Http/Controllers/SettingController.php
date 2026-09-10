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
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'wa_contact' => 'required|string|max:20',
            'shipping_methods' => 'required|array|min:1',
            'shipping_methods.*.courier' => 'required|string|max:100',
            'shipping_methods.*.cost' => 'required|numeric|min:0',
            'shipping_methods.*.eta' => 'required|string|max:50',
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

        \App\Models\Setting::updateOrCreate(['key' => 'wa_contact'], ['value' => $validated['wa_contact']]);
        \App\Models\Setting::updateOrCreate(['key' => 'shipping_methods'], ['value' => $shippingMethods]);
        \App\Models\Setting::updateOrCreate(['key' => 'payment_methods'], ['value' => $paymentMethods]);

        StoreSettings::invalidateCache();

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan toko berhasil disimpan.');
    }
}