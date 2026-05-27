<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    /**
     * Show the payment methods settings page.
     */
    public function index()
    {
        $title    = 'Payment Methods';
        $gateways = PaymentGateway::all()->keyBy('gateway');

        return view('admin.settings.payment-methods', compact('title', 'gateways'));
    }

    /**
     * Save all payment gateway settings.
     */
    public function update(Request $request)
    {
        $data = $request->input('gateways', []);

        foreach ($data as $gatewaySlug => $values) {
            $record = PaymentGateway::where('gateway', $gatewaySlug)->first();
            if (!$record) {
                continue;
            }

            $isEnabled = isset($values['is_enabled']) && $values['is_enabled'] == '1';
            $settings  = $values['settings'] ?? [];

            // Cast all settings values to strings and strip tags for safety
            $cleanSettings = [];
            foreach ($settings as $key => $val) {
                $cleanSettings[$key] = strip_tags((string) ($val ?? ''));
            }

            $record->update([
                'is_enabled' => $isEnabled,
                'settings'   => $cleanSettings,
            ]);
        }

        return redirect()->route('admin.settings.payment-methods')
            ->with('success', 'Payment method settings saved successfully.');
    }
}
