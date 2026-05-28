<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FastagRecharge;
use App\Models\GasBooking;
use App\Models\MobileRecharge;
use App\Models\Orders;
use App\Models\StoreSetting;
use App\Models\Users;
use App\Models\Vendor;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReportsAnalyticsController extends Controller
{
    private function dateRange(Request $request): array
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        return [$startDate, $endDate];
    }

    private function applyDateFilter($query, ?string $column, ?string $startDate, ?string $endDate)
    {
        if (!$column) {
            return $query;
        }

        if (!empty($startDate)) {
            $query->whereDate($column, '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate($column, '<=', $endDate);
        }

        return $query;
    }

    private function firstAvailableColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    public function orderReports(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $status = $request->query('status');

        $query = Orders::with(['customer.user'])->orderByDesc('order_id');
        $this->applyDateFilter($query, 'created_at', $startDate, $endDate);

        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        $orders = $query->get();

        $summary = [
            'total' => (int) $orders->count(),
            'completed' => (int) $orders->whereIn('order_status', ['completed', 'delivered', 'success'])->count(),
            'pending' => (int) $orders->whereIn('order_status', ['pending', 'processing', 'confirmed'])->count(),
            'cancelled' => (int) $orders->whereIn('order_status', ['cancelled', 'rejected', 'failed'])->count(),
        ];

        return view('admin.reports.orders', compact('orders', 'summary', 'startDate', 'endDate', 'status'));
    }

    public function rechargeReports(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $type = $request->query('type');

        $records = collect();

        if (empty($type) || $type === 'mobile') {
            $mobileQuery = MobileRecharge::query()->orderByDesc('mobile_recharge_id');
            $this->applyDateFilter($mobileQuery, 'recharged_at', $startDate, $endDate);
            $records = $records->concat($mobileQuery->get()->map(function ($item) {
                return (object) [
                    'report_type' => 'Mobile Recharge',
                    'reference' => $item->transaction_ref,
                    'customer' => $item->mobile_number,
                    'service' => $item->operator,
                    'amount' => (float) $item->amount,
                    'status' => $item->status,
                    'transaction_date' => $item->recharged_at,
                ];
            }));
        }

        if ((empty($type) || $type === 'fastag') && Schema::hasTable('fastag_recharges')) {
            $fastagQuery = FastagRecharge::query()->orderByDesc('fastag_recharge_id');
            $this->applyDateFilter($fastagQuery, 'recharged_at', $startDate, $endDate);
            $records = $records->concat($fastagQuery->get()->map(function ($item) {
                return (object) [
                    'report_type' => 'FASTag Recharge',
                    'reference' => $item->transaction_ref,
                    'customer' => 'N/A',
                    'service' => 'FASTag',
                    'amount' => (float) $item->amount,
                    'status' => $item->status,
                    'transaction_date' => $item->recharged_at,
                ];
            }));
        }

        if ((empty($type) || $type === 'gas') && Schema::hasTable('gas_bookings')) {
            $gasQuery = GasBooking::query()->orderByDesc('gas_booking_id');
            $this->applyDateFilter($gasQuery, 'booked_at', $startDate, $endDate);
            $records = $records->concat($gasQuery->get()->map(function ($item) {
                return (object) [
                    'report_type' => 'Gas Booking',
                    'reference' => $item->booking_ref,
                    'customer' => $item->customer_name,
                    'service' => $item->provider,
                    'amount' => null,
                    'status' => $item->status,
                    'transaction_date' => $item->booked_at,
                ];
            }));
        }

        $records = $records->sortByDesc(function ($item) {
            return $item->transaction_date ?: Carbon::now()->toDateTimeString();
        })->values();

        $summary = [
            'total' => (int) $records->count(),
            'mobile' => (int) $records->where('report_type', 'Mobile Recharge')->count(),
            'fastag' => (int) $records->where('report_type', 'FASTag Recharge')->count(),
            'gas' => (int) $records->where('report_type', 'Gas Booking')->count(),
        ];

        return view('admin.reports.recharges', compact('records', 'summary', 'startDate', 'endDate', 'type'));
    }

    public function venueBookingReports(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $status = $request->query('status');

        if (!Schema::hasTable('venue_bookings')) {
            return view('admin.reports.venue-bookings', [
                'records' => collect(),
                'summary' => ['total' => 0, 'confirmed' => 0, 'cancelled' => 0, 'revenue' => 0],
                'startDate' => $startDate,
                'endDate' => $endDate,
                'status' => $status,
                'warning' => 'Venue bookings table not found.',
            ]);
        }

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['booking_id', 'id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);
        $dateColumn = $this->firstAvailableColumn('venue_bookings', ['booking_date', 'event_date', 'booked_at', 'created_at']);
        $amountColumn = $this->firstAvailableColumn('venue_bookings', ['amount', 'total_amount', 'booking_amount', 'price']);
        $customerColumn = $this->firstAvailableColumn('venue_bookings', ['customer_name', 'name', 'full_name']);
        $venueNameColumn = $this->firstAvailableColumn('venue_bookings', ['venue_name']);

        $query = DB::table('venue_bookings as vb');

        if (Schema::hasColumn('venue_bookings', 'venue_id') && Schema::hasTable('venues')) {
            $venueIdColumn = $this->firstAvailableColumn('venues', ['id', 'venue_id']);
            $venuesNameColumn = $this->firstAvailableColumn('venues', ['name', 'venue_name']);
            $query->leftJoin('venues as v', 'vb.venue_id', '=', 'v.' . $venueIdColumn);
            $query->addSelect(DB::raw('v.' . $venuesNameColumn . ' as venue_name_from_venues'));
        }

        $query->addSelect(
            DB::raw('vb.' . $idColumn . ' as booking_id'),
            DB::raw($statusColumn ? ('vb.' . $statusColumn . ' as booking_status') : "'pending' as booking_status"),
            DB::raw($dateColumn ? ('vb.' . $dateColumn . ' as booking_date') : 'NULL as booking_date'),
            DB::raw($amountColumn ? ('vb.' . $amountColumn . ' as booking_amount') : '0 as booking_amount'),
            DB::raw($customerColumn ? ('vb.' . $customerColumn . ' as customer_name') : "'N/A' as customer_name"),
            DB::raw($venueNameColumn ? ('vb.' . $venueNameColumn . ' as venue_name') : "NULL as venue_name")
        );

        if (!empty($status) && $statusColumn) {
            $query->where('vb.' . $statusColumn, $status);
        }
        if (!empty($startDate) && $dateColumn) {
            $query->whereDate('vb.' . $dateColumn, '>=', $startDate);
        }
        if (!empty($endDate) && $dateColumn) {
            $query->whereDate('vb.' . $dateColumn, '<=', $endDate);
        }

        $records = $query->orderByDesc('booking_id')->get()->map(function ($item) {
            $item->display_venue_name = $item->venue_name ?? ($item->venue_name_from_venues ?? 'N/A');
            return $item;
        });

        $summary = [
            'total' => (int) $records->count(),
            'confirmed' => (int) $records->filter(fn ($item) => in_array(strtolower((string) $item->booking_status), ['confirmed', 'completed', 'booked'], true))->count(),
            'cancelled' => (int) $records->filter(fn ($item) => in_array(strtolower((string) $item->booking_status), ['cancelled', 'rejected'], true))->count(),
            'revenue' => (float) $records->sum(fn ($item) => (float) ($item->booking_amount ?? 0)),
        ];

        return view('admin.reports.venue-bookings', compact('records', 'summary', 'startDate', 'endDate', 'status'));
    }

    public function revenueReports(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);

        $ordersRevenueQuery = Orders::query()->where('payment_status', 'paid');
        $this->applyDateFilter($ordersRevenueQuery, 'created_at', $startDate, $endDate);
        $orderRevenue = (float) $ordersRevenueQuery->sum('total_amount');

        $mobileRevenueQuery = MobileRecharge::query();
        $this->applyDateFilter($mobileRevenueQuery, 'recharged_at', $startDate, $endDate);
        $mobileRevenue = Schema::hasTable('mobile_recharges') ? (float) $mobileRevenueQuery->sum('amount') : 0.0;

        $fastagRevenue = 0.0;
        if (Schema::hasTable('fastag_recharges')) {
            $fastagRevenueQuery = FastagRecharge::query();
            $this->applyDateFilter($fastagRevenueQuery, 'recharged_at', $startDate, $endDate);
            $fastagRevenue = (float) $fastagRevenueQuery->sum('amount');
        }

        $venueRevenue = 0.0;
        if (Schema::hasTable('venue_bookings')) {
            $amountColumn = $this->firstAvailableColumn('venue_bookings', ['amount', 'total_amount', 'booking_amount', 'price']);
            $dateColumn = $this->firstAvailableColumn('venue_bookings', ['booking_date', 'event_date', 'booked_at', 'created_at']);
            if ($amountColumn) {
                $venueRevenueQuery = DB::table('venue_bookings');
                if (!empty($startDate) && $dateColumn) {
                    $venueRevenueQuery->whereDate($dateColumn, '>=', $startDate);
                }
                if (!empty($endDate) && $dateColumn) {
                    $venueRevenueQuery->whereDate($dateColumn, '<=', $endDate);
                }
                $venueRevenue = (float) $venueRevenueQuery->sum($amountColumn);
            }
        }

        $sources = collect([
            ['label' => 'Orders Revenue', 'count' => (int) $ordersRevenueQuery->count(), 'amount' => $orderRevenue],
            ['label' => 'Mobile Recharge Revenue', 'count' => Schema::hasTable('mobile_recharges') ? (int) $mobileRevenueQuery->count() : 0, 'amount' => $mobileRevenue],
            ['label' => 'FASTag Recharge Revenue', 'count' => Schema::hasTable('fastag_recharges') ? (int) (isset($fastagRevenueQuery) ? $fastagRevenueQuery->count() : 0) : 0, 'amount' => $fastagRevenue],
            ['label' => 'Venue Booking Revenue', 'count' => Schema::hasTable('venue_bookings') ? (int) DB::table('venue_bookings')->count() : 0, 'amount' => $venueRevenue],
        ]);

        $summary = [
            'total_revenue' => (float) ($orderRevenue + $mobileRevenue + $fastagRevenue + $venueRevenue),
            'order_revenue' => $orderRevenue,
            'recharge_revenue' => (float) ($mobileRevenue + $fastagRevenue),
            'venue_revenue' => $venueRevenue,
            'total_orders' => (int) Orders::count(),
            'active_vendors' => (int) Vendor::whereIn('status', ['1', 1])->count(),
            'total_users' => (int) Users::where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)->count(),
        ];

        $monthlySales = Orders::selectRaw('MONTH(created_at) as month_no, SUM(total_amount) as total_sales')
            ->whereYear('created_at', now()->year)
            ->groupBy('month_no')
            ->orderBy('month_no')
            ->get();
        $chartLabels = $monthlySales->map(fn ($item) => Carbon::create()->month((int) $item->month_no)->format('F'))->values();
        $chartData = $monthlySales->map(fn ($item) => round((float) $item->total_sales, 2))->values();

        return view('admin.reports.revenue', compact('sources', 'summary', 'startDate', 'endDate', 'chartLabels', 'chartData'));
    }

    public function exportOrderReportExcel(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $status = $request->query('status');

        $query = Orders::with(['customer.user'])->orderByDesc('order_id');
        $this->applyDateFilter($query, 'created_at', $startDate, $endDate);
        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        $orders = $query->get();

        $fileName = 'order-report-' . date('Y-m-d-H-i-s') . '.xls';
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            echo "S.No.\tOrder ID\tCustomer\tAmount (INR)\tPayment Status\tOrder Status\tDate\n";
            foreach ($orders as $index => $order) {
                echo ($index + 1) . "\t";
                echo ('#' . ($order->order_number ?? '')) . "\t";
                echo (optional(optional($order->customer)->user)->name ?? 'N/A') . "\t";
                echo number_format((float)$order->total_amount, 2, '.', '') . "\t";
                echo ucfirst((string)($order->payment_status ?? '')) . "\t";
                echo ucfirst(str_replace('_', ' ', (string)($order->order_status ?? ''))) . "\t";
                echo (optional($order->created_at)->format('d-m-Y') ?? '') . "\n";
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportOrderReportPdf(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $status = $request->query('status');

        $query = Orders::with(['customer.user'])->orderByDesc('order_id');
        $this->applyDateFilter($query, 'created_at', $startDate, $endDate);
        if (!empty($status)) {
            $query->where('order_status', $status);
        }

        $orders = $query->get();

        $summary = [
            'total'     => (int) $orders->count(),
            'completed' => (int) $orders->whereIn('order_status', ['completed', 'delivered', 'success'])->count(),
            'pending'   => (int) $orders->whereIn('order_status', ['pending', 'processing', 'confirmed'])->count(),
            'cancelled' => (int) $orders->whereIn('order_status', ['cancelled', 'rejected', 'failed'])->count(),
        ];

        $storeSetting = StoreSetting::first();

        $pdf = Pdf::loadView('admin.reports.ordersReportExportPdf', compact('orders', 'summary', 'storeSetting', 'startDate', 'endDate', 'status'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('order-report-' . date('Y-m-d-H-i-s') . '.pdf');
    }
}
