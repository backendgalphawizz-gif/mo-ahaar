<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MobileRecharge;
use App\Models\MobileRechargePlan;
use App\Models\FastagAccount;
use App\Models\FastagRecharge;
use App\Models\GasBooking;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RechargeManagementController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.mobile-recharge.index');
    }

    public function mobileRechargeIndex()
    {
        $title = 'Mobile Recharge';
        $operators = MobileRechargePlan::where('status', 1)->select('operator')->distinct()->pluck('operator');
        $plans = MobileRechargePlan::where('status', 1)->orderBy('amount')->get();
        $recharges = MobileRecharge::leftJoin('mobile_recharge_plans', 'mobile_recharge_plans.plan_id', '=', 'mobile_recharges.plan_id')
            ->select('mobile_recharges.*', 'mobile_recharge_plans.plan_name')
            ->orderByDesc('mobile_recharge_id')
            ->get();

        return view('admin.recharge.mobileRecharge', compact('title', 'operators', 'plans', 'recharges'));
    }

    public function getPlansByOperator($operator)
    {
        $plans = MobileRechargePlan::where('status', 1)
            ->where('operator', $operator)
            ->orderBy('amount')
            ->get(['plan_id', 'plan_name', 'amount', 'validity_days', 'benefits']);

        return response()->json($plans);
    }

    public function storeMobileRecharge(Request $request)
    {
        $validated = $request->validate([
            'mobile_number' => ['required', 'digits:10'],
            'operator' => ['required', 'string', 'max:80'],
            'plan_id' => ['nullable', 'exists:mobile_recharge_plans,plan_id'],
            'amount' => ['required', 'numeric', 'min:10'],
        ]);

        MobileRecharge::create([
            'mobile_number' => $validated['mobile_number'],
            'operator' => $validated['operator'],
            'plan_id' => $validated['plan_id'] ?? null,
            'amount' => $validated['amount'],
            'transaction_ref' => 'MOB' . now()->format('YmdHis') . rand(1000, 9999),
            'status' => 'success',
            'recharged_at' => now(),
        ]);

        return redirect()->route('admin.mobile-recharge.index')->with('success', 'Recharge completed instantly.');
    }

    public function fastagIndex()
    {
        $title = 'FASTag Recharge';
        $accounts = FastagAccount::orderByDesc('fastag_account_id')->get();
        $transactions = FastagRecharge::join('fastag_accounts', 'fastag_accounts.fastag_account_id', '=', 'fastag_recharges.fastag_account_id')
            ->select('fastag_recharges.*', 'fastag_accounts.vehicle_number', 'fastag_accounts.provider', 'fastag_accounts.tag_id')
            ->orderByDesc('fastag_recharge_id')
            ->get();

        return view('admin.recharge.fastagRecharge', compact('title', 'accounts', 'transactions'));
    }

    public function storeFastagAccount(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => ['required', 'string', 'max:20', Rule::unique('fastag_accounts', 'vehicle_number')],
            'provider' => ['required', 'string', 'max:80'],
            'tag_id' => ['required', 'string', 'max:60', Rule::unique('fastag_accounts', 'tag_id')],
            'current_balance' => ['nullable', 'numeric', 'min:0'],
        ]);

        FastagAccount::create([
            'vehicle_number' => strtoupper($validated['vehicle_number']),
            'provider' => $validated['provider'],
            'tag_id' => strtoupper($validated['tag_id']),
            'current_balance' => $validated['current_balance'] ?? 0,
            'status' => 1,
        ]);

        return redirect()->route('admin.fastag.index')->with('success', 'FASTag linked successfully.');
    }

    public function storeFastagRecharge(Request $request)
    {
        $validated = $request->validate([
            'fastag_account_id' => ['required', 'exists:fastag_accounts,fastag_account_id'],
            'amount' => ['required', 'numeric', 'min:50'],
        ]);

        DB::transaction(function () use ($validated) {
            FastagRecharge::create([
                'fastag_account_id' => $validated['fastag_account_id'],
                'amount' => $validated['amount'],
                'transaction_ref' => 'FST' . now()->format('YmdHis') . rand(1000, 9999),
                'status' => 'success',
                'recharged_at' => now(),
            ]);

            $account = FastagAccount::find($validated['fastag_account_id']);
            $account->current_balance = (float) $account->current_balance + (float) $validated['amount'];
            $account->save();
        });

        return redirect()->route('admin.fastag.index')->with('success', 'FASTag recharge successful.');
    }

    public function gasBookingIndex()
    {
        $title = 'Gas Cylinder Booking';
        $providers = ['Indane', 'HP Gas', 'Bharat Gas'];
        $bookings = GasBooking::orderByDesc('gas_booking_id')->get();

        return view('admin.recharge.gasBooking', compact('title', 'providers', 'bookings'));
    }

    public function storeGasBooking(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z\s\'\-\.]+$/'],
            'mobile_number' => ['required', 'digits:10'],
            'provider' => ['required', 'string', 'max:80'],
            'consumer_number' => ['required', 'string', 'max:40'],
            'delivery_eta' => ['nullable', 'date'],
        ], [
            'customer_name.regex' => 'Customer name may only contain letters, spaces, hyphens, and apostrophes.',
        ]);

        GasBooking::create([
            'customer_name' => $validated['customer_name'],
            'mobile_number' => $validated['mobile_number'],
            'provider' => $validated['provider'],
            'consumer_number' => strtoupper($validated['consumer_number']),
            'booking_ref' => 'GAS' . now()->format('YmdHis') . rand(1000, 9999),
            'status' => 'booked',
            'booked_at' => now(),
            'delivery_eta' => $validated['delivery_eta'] ?? null,
        ]);

        return redirect()->route('admin.gas-booking.index')->with('success', 'Gas cylinder booked successfully.');
    }

    public function updateGasBookingStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['booked', 'confirmed', 'in_transit', 'delivered', 'cancelled'])],
        ]);

        $booking = GasBooking::findOrFail($id);
        $booking->status = $validated['status'];
        $booking->save();

        return redirect()->route('admin.gas-booking.index')->with('success', 'Booking status updated.');
    }
}