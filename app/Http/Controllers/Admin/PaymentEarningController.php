<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionSettlement;
use App\Models\Orders;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentEarningController extends Controller
{
    private function applyActiveVendorFilter(Builder $query): Builder
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('vendors', 'approval_status')) {
            return $query->where('approval_status', 'approved');
        }

        return $query->whereIn('status', ['1', 1]);
    }

    public function commissionSettings(Request $request)
    {
        $title = 'Set Commission Percentage';
        $search = trim((string) $request->query('search', ''));

        $vendorsQuery = Vendor::select('vendor_id', 'business_name', 'owner_name', 'email', 'mobile', 'commission_percent', 'status')
            ->orderBy('business_name');

        if ($search !== '') {
            $vendorsQuery->where(function ($query) use ($search) {
                $query->where('business_name', 'like', '%' . $search . '%')
                    ->orWhere('owner_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%');
            });
        }

        $vendors = $vendorsQuery->get();

        return view('admin.payments.commissionSettings', compact('title', 'vendors', 'search'));
    }

    public function updateCommissionPercentage(Request $request, $vendorId)
    {
        $validated = $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        $vendor = Vendor::findOrFail($vendorId);
        $vendor->commission_percent = round((float) $validated['commission_percent'], 2);
        $vendor->save();

        return back()->with('success', 'Commission percentage updated successfully.');
    }

    public function paymentStatusTracking(Request $request)
    {
        $title = 'Payment Status Tracking';
        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = Orders::with(['vendor', 'customer.user'])->orderByDesc('order_id');
        if (!empty($status) && in_array($status, ['pending', 'paid', 'failed', 'refunded'], true)) {
            $query->where('payment_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('business_name', 'like', '%' . $search . '%')
                            ->orWhere('owner_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('customer.user', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('mobile', 'like', '%' . $search . '%');
                    });
            });
        }

        $orders = $query->get();

        $stats = [
            'total' => Orders::count(),
            'paid' => Orders::where('payment_status', 'paid')->count(),
            'pending' => Orders::where('payment_status', 'pending')->count(),
            'failed' => Orders::where('payment_status', 'failed')->count(),
            'refunded' => Orders::where('payment_status', 'refunded')->count(),
        ];

        return view('admin.payments.paymentStatus', compact('title', 'orders', 'stats', 'status', 'search'));
    }

    public function vendorTransactions(Request $request)
    {
        $title = 'Vendor Transactions';
        $vendorId = $request->query('vendor_id');
        $search = trim((string) $request->query('search', ''));

        $vendors = $this->applyActiveVendorFilter(
            Vendor::select('vendor_id', 'business_name', 'owner_name')
        )
            // ->where('status', '1')
            ->orderBy('business_name')

            ->get();

        $transactionsQuery = Orders::with(['vendor', 'customer.user'])
            ->whereNotNull('vendor_id')
            ->whereHas('vendor', function (Builder $vendorQuery) {
                $this->applyActiveVendorFilter($vendorQuery);
            })
            ->orderByDesc('order_id');

        if (!empty($vendorId)) {
            $transactionsQuery->where('vendor_id', (int) $vendorId);
        }

        if ($search !== '') {
            $transactionsQuery->where(function ($query) use ($search) {
                $query->where('order_number', 'like', '%' . $search . '%')
                    ->orWhere('payment_method', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhereHas('vendor', function (Builder $vendorQuery) use ($search) {
                        $this->applyActiveVendorFilter($vendorQuery)
                            ->where(function (Builder $nameQuery) use ($search) {
                                $nameQuery->where('business_name', 'like', '%' . $search . '%')
                                    ->orWhere('owner_name', 'like', '%' . $search . '%');
                            });
                    })
                    ->orWhereHas('customer.user', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%')
                            ->orWhere('mobile', 'like', '%' . $search . '%');
                    });
            });
        }

        $transactions = $transactionsQuery->get();

        $paidBase = Orders::where('payment_status', 'paid')
            ->whereNotNull('vendor_id')
            ->whereHas('vendor', function (Builder $vendorQuery) {
                $this->applyActiveVendorFilter($vendorQuery);
            });
        if (!empty($vendorId)) {
            $paidBase->where('vendor_id', (int) $vendorId);
        }

        $summary = [
            'paid_count' => (clone $paidBase)->count(),
            'paid_total' => (float) (clone $paidBase)->sum('total_amount'),
            'all_count' => $transactions->count(),
        ];

        return view('admin.payments.vendorTransactions', compact('title', 'vendors', 'transactions', 'summary', 'vendorId', 'search'));
    }

    public function commissionSettlements()
    {
        $title = 'Payment Requests';
        $vendors = Vendor::select('vendor_id', 'business_name', 'owner_name', 'commission_percent')->orderBy('business_name')->get();
        $settlements = CommissionSettlement::with('vendor')->orderByDesc('settlement_id')->get();
        return view('admin.payments.commissionSettlements', compact('title', 'vendors', 'settlements'));
    }

    public function commissionSettlementDetail($id)
    {
        $title = 'Payment Request';
        $settlement = CommissionSettlement::with('vendor')->findOrFail($id);
        return view('admin.payments.commissionSettlementDetail', compact('title', 'settlement'));
    }

    public function storeCommissionSettlement(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,vendor_id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'request_note' => 'nullable|string|max:1000',
        ]);

        $vendor = Vendor::findOrFail($validated['vendor_id']);

        $grossSales = (float) Orders::where('vendor_id', $vendor->vendor_id)
            ->where('payment_status', 'paid')
            ->whereDate('created_at', '>=', $validated['period_start'])
            ->whereDate('created_at', '<=', $validated['period_end'])
            ->sum('total_amount');

        $rate = (float) ($vendor->commission_percent ?? 0);
        $commissionAmount = round(($grossSales * $rate) / 100, 2);
        $payoutAmount = round(max($grossSales - $commissionAmount, 0), 2);

        CommissionSettlement::create([
            'vendor_id' => $vendor->vendor_id,
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'gross_sales' => $grossSales,
            'commission_rate' => $rate,
            'commission_amount' => $commissionAmount,
            'payout_amount' => $payoutAmount,
            'status' => 'pending',
            'request_note' => $validated['request_note'] ?? null,
            'requested_at' => now(),
        ]);

        return redirect()->route('admin.payments.settlements')->with('success', 'Commission settlement request submitted.');
    }

    public function updateCommissionSettlementStatus(Request $request, $id)
    {
        $settlement = CommissionSettlement::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'approved', 'rejected', 'paid'])],
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $settlement->status = $validated['status'];
        $settlement->admin_note = $validated['admin_note'] ?? null;
        $settlement->processed_at = now();

        if ($validated['status'] === 'paid') {
            $settlement->paid_at = now();
        }

        $settlement->save();

        return redirect()->route('admin.payments.settlements')->with('success', 'Payout status updated successfully.');
    }
}
