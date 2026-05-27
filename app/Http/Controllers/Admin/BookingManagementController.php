<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BookingManagementController extends Controller
{
    private function firstAvailableColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    public function index(Request $request)
    {
        if (!Schema::hasTable('venue_bookings')) {
            return view('admin.bookings.index', [
                'bookings' => collect(),
                'summary' => [
                    'total' => 0,
                    'pending' => 0,
                    'confirmed' => 0,
                    'cancelled' => 0,
                ],
                'selectedStatus' => null,
                'warning' => 'Venue bookings table not found.',
            ]);
        }

        $selectedStatus = $request->query('status');

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['booking_id', 'id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);
        $dateColumn = $this->firstAvailableColumn('venue_bookings', ['booking_date', 'event_date', 'booked_at', 'created_at']);
        $amountColumn = $this->firstAvailableColumn('venue_bookings', ['amount', 'total_amount', 'booking_amount', 'price']);
        $customerColumn = $this->firstAvailableColumn('venue_bookings', ['customer_name', 'name', 'full_name']);
        $phoneColumn = $this->firstAvailableColumn('venue_bookings', ['phone', 'mobile', 'contact_phone']);
        $venueNameColumn = $this->firstAvailableColumn('venue_bookings', ['venue_name']);
        $createdAtColumn = Schema::hasColumn('venue_bookings', 'created_at') ? 'created_at' : null;
        $updatedAtColumn = Schema::hasColumn('venue_bookings', 'updated_at') ? 'updated_at' : null;

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
            DB::raw($dateColumn ? ('vb.' . $dateColumn . ' as booking_date') : 'vb.created_at as booking_date'),
            DB::raw($amountColumn ? ('vb.' . $amountColumn . ' as booking_amount') : '0 as booking_amount'),
            DB::raw($customerColumn ? ('vb.' . $customerColumn . ' as customer_name') : "'N/A' as customer_name"),
            DB::raw($phoneColumn ? ('vb.' . $phoneColumn . ' as customer_phone') : "'N/A' as customer_phone"),
            DB::raw($venueNameColumn ? ('vb.' . $venueNameColumn . ' as venue_name') : "NULL as venue_name")
        );

        if ($createdAtColumn) {
            $query->addSelect(DB::raw('vb.' . $createdAtColumn . ' as created_at'));
        }
        if ($updatedAtColumn) {
            $query->addSelect(DB::raw('vb.' . $updatedAtColumn . ' as updated_at'));
        }

        if (!empty($selectedStatus) && $statusColumn) {
            $query->where('vb.' . $statusColumn, $selectedStatus);
        }

        $bookings = $query->orderByDesc('booking_id')->get()->map(function ($booking) {
            $booking->display_venue_name = $booking->venue_name ?? ($booking->venue_name_from_venues ?? 'N/A');
            return $booking;
        });

        $summary = [
            'total' => (int) $bookings->count(),
            'pending' => (int) $bookings->filter(function ($b) {
                return in_array(strtolower((string) $b->booking_status), ['pending', 'booked', 'cancel_requested'], true);
            })->count(),
            'confirmed' => (int) $bookings->filter(function ($b) {
                return in_array(strtolower((string) $b->booking_status), ['confirmed', 'in_progress', 'completed'], true);
            })->count(),
            'cancelled' => (int) $bookings->filter(function ($b) {
                return in_array(strtolower((string) $b->booking_status), ['cancelled', 'rejected'], true);
            })->count(),
        ];

        return view('admin.bookings.index', compact('bookings', 'summary', 'selectedStatus'));
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Schema::hasTable('venue_bookings')) {
            return back()->with('error', 'Venue bookings table not found.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,booked,confirmed,in_progress,completed,cancel_requested,cancelled,rejected',
        ]);

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['booking_id', 'id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);

        if (!$statusColumn) {
            return back()->with('error', 'Booking status column not found.');
        }

        $updatePayload = [
            $statusColumn => $validated['status'],
        ];
        if (Schema::hasColumn('venue_bookings', 'updated_at')) {
            $updatePayload['updated_at'] = now();
        }

        $updated = DB::table('venue_bookings')
            ->where($idColumn, $id)
            ->update($updatePayload);

        if (!$updated) {
            return back()->with('error', 'Booking not found or status unchanged.');
        }

        return back()->with('success', 'Booking status updated successfully.');
    }

    public function handleCancellation(Request $request, $id)
    {
        if (!Schema::hasTable('venue_bookings')) {
            return back()->with('error', 'Venue bookings table not found.');
        }

        $validated = $request->validate([
            'action' => 'required|in:approve,reject,force_cancel',
        ]);

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['booking_id', 'id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);

        if (!$statusColumn) {
            return back()->with('error', 'Booking status column not found.');
        }

        $newStatus = match ($validated['action']) {
            'approve', 'force_cancel' => 'cancelled',
            'reject' => 'confirmed',
        };

        $updatePayload = [
            $statusColumn => $newStatus,
        ];
        if (Schema::hasColumn('venue_bookings', 'updated_at')) {
            $updatePayload['updated_at'] = now();
        }

        $updated = DB::table('venue_bookings')
            ->where($idColumn, $id)
            ->update($updatePayload);

        if (!$updated) {
            return back()->with('error', 'Booking not found or cancellation action failed.');
        }

        return back()->with('success', 'Booking cancellation handled successfully.');
    }
}