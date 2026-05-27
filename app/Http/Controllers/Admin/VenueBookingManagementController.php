<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class VenueBookingManagementController extends Controller
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

    private function normalizeVenueStatus($status)
    {
        if (is_numeric($status)) {
            return (int) $status === 1 ? 1 : 0;
        }

        $value = strtolower((string) $status);
        return in_array($value, ['1', 'active', 'approved', 'enabled'], true) ? 1 : 0;
    }

    private function nextVenueStatusDbValue($currentStatus)
    {
        $isActive = $this->normalizeVenueStatus($currentStatus) === 1;

        if (is_numeric($currentStatus)) {
            return $isActive ? 0 : 1;
        }

        $statusStr = strtolower((string) $currentStatus);
        if (in_array($statusStr, ['active', 'inactive'], true)) {
            return $isActive ? 'inactive' : 'active';
        }

        return $isActive ? '0' : '1';
    }

    private function pickValue(object $record, array $candidates, $default = null)
    {
        foreach ($candidates as $candidate) {
            if (property_exists($record, $candidate) && $record->{$candidate} !== null && $record->{$candidate} !== '') {
                return $record->{$candidate};
            }
        }

        return $default;
    }

    public function listings()
    {
        if (!Schema::hasTable('venues')) {
            return view('admin.venues.listings', [
                'venues' => collect(),
                'summary' => ['total' => 0, 'active' => 0, 'inactive' => 0],
                'warning' => 'Venues table not found.',
            ]);
        }

        $idColumn = $this->firstAvailableColumn('venues', ['id', 'venue_id']);
        $nameColumn = $this->firstAvailableColumn('venues', ['name', 'venue_name']);
        $priceColumn = $this->firstAvailableColumn('venues', ['price_per_day', 'price_per_booking']);

        $query = DB::table('venues as v')
            ->leftJoin('vendors as vd', 'v.vendor_id', '=', 'vd.vendor_id')
            ->select(
                DB::raw('v.' . $idColumn . ' as venue_id'),
                DB::raw('v.vendor_id as vendor_id'),
                DB::raw('v.' . $nameColumn . ' as venue_name'),
                'v.city',
                'v.capacity',
                DB::raw('v.' . $priceColumn . ' as price_per_booking'),
                'v.status',
                'v.image',
                'v.created_at',
                'vd.business_name',
                'vd.owner_name',
                'vd.status as vendor_status'
            );

        $query->orderByDesc('venue_id');
        $venues = $query->get();

        $summary = [
            'total' => (int) $venues->count(),
            'active' => (int) $venues->filter(fn ($v) => $this->normalizeVenueStatus($v->status) === 1)->count(),
            'inactive' => (int) $venues->filter(fn ($v) => $this->normalizeVenueStatus($v->status) === 0)->count(),
        ];

        return view('admin.venues.listings', compact('venues', 'summary'));
    }

    public function viewVenue($id)
    {
        if (!Schema::hasTable('venues')) {
            return redirect()->route('admin.venues.listings')->with('error', 'Venues table not found.');
        }

        $idColumn = $this->firstAvailableColumn('venues', ['id', 'venue_id']);
        $venueRaw = DB::table('venues')->where($idColumn, $id)->first();

        if (!$venueRaw) {
            return redirect()->route('admin.venues.listings')->with('error', 'Venue not found.');
        }

        $vendor = null;
        if (property_exists($venueRaw, 'vendor_id') && !empty($venueRaw->vendor_id) && Schema::hasTable('vendors')) {
            $vendor = DB::table('vendors')
                ->where('vendor_id', $venueRaw->vendor_id)
                ->select('vendor_id', 'business_name', 'owner_name', 'mobile', 'email', 'status')
                ->first();
        }

        $venue = (object) [
            'id' => $id,
            'vendor_id' => $this->pickValue($venueRaw, ['vendor_id']),
            'name' => $this->pickValue($venueRaw, ['name', 'venue_name'], 'N/A'),
            'type' => $this->pickValue($venueRaw, ['venue_type', 'type'], 'N/A'),
            'contact_name' => $this->pickValue($venueRaw, ['contact_name'], 'N/A'),
            'contact_phone' => $this->pickValue($venueRaw, ['contact_phone'], 'N/A'),
            'contact_email' => $this->pickValue($venueRaw, ['email'], 'N/A'),
            'address' => $this->pickValue($venueRaw, ['address'], 'N/A'),
            'city' => $this->pickValue($venueRaw, ['city'], 'N/A'),
            'state' => $this->pickValue($venueRaw, ['state'], 'N/A'),
            'pincode' => $this->pickValue($venueRaw, ['pincode'], 'N/A'),
            'capacity' => $this->pickValue($venueRaw, ['capacity'], 'N/A'),
            'price_per_booking' => (float) $this->pickValue($venueRaw, ['price_per_day', 'price_per_booking'], 0),
            'image' => $this->pickValue($venueRaw, ['image']),
            'description' => $this->pickValue($venueRaw, ['description'], 'No description provided.'),
            'status' => $this->pickValue($venueRaw, ['status'], 0),
            'created_at' => $this->pickValue($venueRaw, ['created_at']),
            'updated_at' => $this->pickValue($venueRaw, ['updated_at']),
        ];

        return view('admin.venues.view', compact('venue', 'vendor'));
    }

    public function approveVenueVendor($vendorId)
    {
        $vendor = Vendor::where('vendor_id', $vendorId)->first();
        if (!$vendor) {
            return back()->with('error', 'Vendor not found.');
        }

        $vendor->status = '1';
        $vendor->save();

        if (!empty($vendor->user_id)) {
            Users::where('user_id', $vendor->user_id)->update(['status' => 1]);
        }

        return back()->with('success', 'Venue vendor approved successfully.');
    }

    public function toggleVenueStatus($id)
    {
        if (!Schema::hasTable('venues')) {
            return back()->with('error', 'Venues table not found.');
        }

        $idColumn = $this->firstAvailableColumn('venues', ['id', 'venue_id']);
        $venue = DB::table('venues')->where($idColumn, $id)->first();

        if (!$venue) {
            return back()->with('error', 'Venue not found.');
        }

        $newValue = $this->nextVenueStatusDbValue($venue->status);

        DB::table('venues')->where($idColumn, $id)->update([
            'status' => $newValue,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Venue listing status updated successfully.');
    }

    public function deleteVenue($id)
    {
        if (!Schema::hasTable('venues')) {
            return back()->with('error', 'Venues table not found.');
        }

        $idColumn = $this->firstAvailableColumn('venues', ['id', 'venue_id']);

        $deleted = DB::table('venues')->where($idColumn, $id)->delete();

        if (!$deleted) {
            return back()->with('error', 'Venue not found or already deleted.');
        }

        return back()->with('success', 'Fake venue listing deleted successfully.');
    }

    public function bookings()
    {
        if (!Schema::hasTable('venue_bookings')) {
            return view('admin.venues.bookings', [
                'bookings' => collect(),
                'warning' => 'Venue bookings table not found.',
            ]);
        }

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['id', 'booking_id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);
        $dateColumn = $this->firstAvailableColumn('venue_bookings', ['booking_date', 'event_date', 'booked_at', 'created_at']);
        $amountColumn = $this->firstAvailableColumn('venue_bookings', ['amount', 'total_amount', 'booking_amount', 'price']);
        $customerColumn = $this->firstAvailableColumn('venue_bookings', ['customer_name', 'name', 'full_name']);
        $phoneColumn = $this->firstAvailableColumn('venue_bookings', ['phone', 'mobile', 'contact_phone']);
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
            DB::raw($dateColumn ? ('vb.' . $dateColumn . ' as booking_date') : 'vb.created_at as booking_date'),
            DB::raw($amountColumn ? ('vb.' . $amountColumn . ' as booking_amount') : '0 as booking_amount'),
            DB::raw($customerColumn ? ('vb.' . $customerColumn . ' as customer_name') : "'N/A' as customer_name"),
            DB::raw($phoneColumn ? ('vb.' . $phoneColumn . ' as customer_phone') : "'N/A' as customer_phone"),
            DB::raw($venueNameColumn ? ('vb.' . $venueNameColumn . ' as venue_name') : "NULL as venue_name")
        );

        $bookings = $query->orderByDesc('booking_id')->get()->map(function ($booking) {
            $booking->display_venue_name = $booking->venue_name ?? ($booking->venue_name_from_venues ?? 'N/A');
            return $booking;
        });

        return view('admin.venues.bookings', compact('bookings'));
    }

    public function updateBookingStatus(Request $request, $id)
    {
        if (!Schema::hasTable('venue_bookings')) {
            return back()->with('error', 'Venue bookings table not found.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,booked,confirmed,in_progress,completed,cancelled,rejected',
        ]);

        $idColumn = $this->firstAvailableColumn('venue_bookings', ['id', 'booking_id']);
        $statusColumn = $this->firstAvailableColumn('venue_bookings', ['status', 'booking_status', 'order_status']);

        if (!$statusColumn) {
            return back()->with('error', 'Booking status column not found.');
        }

        $updated = DB::table('venue_bookings')
            ->where($idColumn, $id)
            ->update([
                $statusColumn => $validated['status'],
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return back()->with('error', 'Booking not found or status unchanged.');
        }

        return back()->with('success', 'Venue booking status updated successfully.');
    }
}
