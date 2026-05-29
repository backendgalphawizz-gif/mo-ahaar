<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Orders;
use App\Models\OrderTracking;
use App\Models\PaymentGateway;
use App\Models\StoreSetting;
use App\Models\Users;

class OrderManagementController extends Controller
{
   private function isVendorPanel(): bool
   {
      return (int) session('role_type') === 3;
   }

   private function currentVendorId(): ?int
   {
      $vendorId = session('vendor_id');
      return $vendorId ? (int) $vendorId : null;
   }

   private function scopedOrdersQuery()
   {
      $query = Orders::query();
      if ($this->isVendorPanel()) {
         $query->where('vendor_id', $this->currentVendorId());
      }

      return $query;
   }

   public static function orderStatusGroups(): array
   {
      return [
         'new' => ['pending', 'payment_pending'],
         'accepted' => ['accepted', 'confirmed', 'processing'],
         'rejected' => ['rejected'],
         'picked_up' => ['picked_up'],
         'out_for_delivery' => ['out_for_delivery', 'shipped'],
         'delivered' => ['delivered', 'completed', 'success'],
         'cancelled' => ['cancelled'],
      ];
   }

      public function orders(Request $request)
    {
      $title = 'Order Management';
          $query = $this->scopedOrdersQuery()->with([
             'customer.user',
             'vendor',
             'orderItems',
             'deliveryAssignment.driver',
          ])->orderByDesc('order_id');

          $search = trim((string) $request->query('search', ''));
          if ($search !== '') {
             $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhereHas('customer.user', function ($u) use ($search) {
                     $u->where('name', 'like', "%$search%")
                        ->orWhere('mobile', 'like', "%$search%");
                  })
                  ->orWhereHas('vendor', function ($v) use ($search) {
                     $v->where('business_name', 'like', "%$search%");
                  })
                  ->orWhere('payment_method', 'like', "%$search%");
             });
          }

         $statusFilter = $request->query('status_filter');
         if ($statusFilter && isset(self::orderStatusGroups()[$statusFilter])) {
             $query->whereIn('order_status', self::orderStatusGroups()[$statusFilter]);
         }

         if ($request->query('scope') === 'incoming') {
             $query->where('order_status', 'pending');
         }

         if ($request->filled('date_from')) {
             $query->whereDate('created_at', '>=', $request->query('date_from'));
         }
         if ($request->filled('date_to')) {
             $query->whereDate('created_at', '<=', $request->query('date_to'));
         }

         $statusCounts = [];
         foreach (self::orderStatusGroups() as $key => $statuses) {
             $statusCounts[$key] = $this->scopedOrdersQuery()->whereIn('order_status', $statuses)->count();
         }
         $statusCounts['total'] = (int) $this->scopedOrdersQuery()->count();

         $availableDrivers = Users::where('role_type', Users::DRIVER_APP_ROLE_TYPE)
             ->where('approval_status', 'approved')
             ->where('status', '1')
             ->orderBy('name')
             ->get(['user_id', 'name', 'mobile']);

         $allOrders = $query->paginate(15)->withQueryString();

      return view('admin.orders.ordersList', compact('title', 'allOrders', 'statusCounts', 'availableDrivers', 'search'));
   }

   public function assignDriver(Request $request, $id)
   {
      $order = $this->scopedOrdersQuery()->with(['vendor', 'customer.user'])->find($id);
      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $validated = $request->validate([
         'driver_id' => ['required', 'integer', Rule::exists('users', 'user_id')->where('role_type', Users::DRIVER_APP_ROLE_TYPE)],
      ]);

      $driver = Users::where('user_id', $validated['driver_id'])
         ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
         ->where('approval_status', 'approved')
         ->first();

      if (!$driver) {
         return back()->with('error', 'Selected driver is not available for assignment.');
      }

      try {
         $shipAddr = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : $order->shipping_address;

         $deliveryAddress = is_array($shipAddr)
            ? trim(implode(', ', array_filter([
               $shipAddr['address_line'] ?? null,
               $shipAddr['city'] ?? null,
               $shipAddr['pincode'] ?? null,
            ])))
            : (string) ($order->shipping_address ?? '');

         $assignment = \App\Models\DeliveryAssignment::firstOrNew(['order_id' => $order->order_id]);
         $assignment->driver_id = $driver->user_id;
         $assignment->status = \App\Models\DeliveryAssignment::STATUS_ASSIGNED;
         $assignment->assigned_at = now();
         $assignment->store_name = $order->vendor?->business_name;
         $assignment->delivery_address = $deliveryAddress ?: $assignment->delivery_address;
         $assignment->payout_amount = $assignment->payout_amount ?: round((float) $order->total_amount * 0.05, 2);
         $assignment->save();

         if (in_array($order->order_status, ['pending', 'accepted', 'confirmed', 'processing'], true)) {
            $order->update(['order_status' => 'processing']);
         }

         OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $order->order_status,
            'description' => 'Delivery partner ' . $driver->name . ' assigned by admin.',
            'location' => 'Admin Panel',
            'tracked_at' => now(),
         ]);

         \App\Http\Controllers\API\DriverApp\NotificationController::notify(
            (int) $driver->user_id,
            'New Delivery Assigned',
            'Admin assigned order ' . ($order->order_number ?? $order->order_id) . ' to you.',
            'new_delivery_assigned',
            $assignment
         );

         return back()->with('success', 'Delivery partner assigned successfully.');
      } catch (\Exception $e) {
         return back()->with('error', 'Failed to assign driver: ' . $e->getMessage());
      }
   }

   public function addOrder()
   {
      $title = 'Add Order';
      $customers = DB::table('customers')
         ->leftJoin('users', 'customers.user_id', '=', 'users.user_id')
         ->select('customers.customer_id', 'users.name', 'users.email')
         ->where('users.status', 1)
         ->orderBy('users.name')
         ->get();

      // $vendors removed

      $activePaymentMethods = PaymentGateway::where('is_enabled', true)
         ->select('gateway', 'display_name')
         ->orderBy('id')
         ->get();

      return view('admin.orders.addOrder', compact('title', 'customers', 'activePaymentMethods'));
   }

   public function storeOrder(Request $request)
   {
      $activePaymentMethods = PaymentGateway::where('is_enabled', true)->pluck('gateway')->toArray();

      if (empty($activePaymentMethods)) {
         return back()->withInput()->with('error', 'No active payment method is configured. Please enable one in Payment Methods settings.');
      }

      $validated = $request->validate([
         'customer_id' => 'required|integer',
         // 'vendor_id' removed
         'payment_method' => ['required', 'string', 'max:50', Rule::in($activePaymentMethods)],
         'payment_status' => 'required|string|max:30',
         'order_status' => ['required', 'string', 'max:30', Rule::in(Orders::persistableOrderStatuses())],
         'subtotal' => 'nullable|numeric|min:0',
         'tax_amount' => 'nullable|numeric|min:0',
         'shipping_amount' => 'nullable|numeric|min:0',
         'total_amount' => 'required|numeric|min:0',
         'shipping_address' => 'nullable|string',
         'notes' => 'nullable|string',
      ]);

      $isActiveCustomer = DB::table('customers')
         ->leftJoin('users', 'customers.user_id', '=', 'users.user_id')
         ->where('customers.customer_id', $validated['customer_id'])
         ->where('users.status', 1)
         ->exists();

      if (!$isActiveCustomer) {
         return back()->withInput()->with('error', 'Only active customers can be selected for new orders.');
      }

      // Vendor validation removed

      try {
         // Generate unique order number for admin panel orders
         $validated['order_number'] = 'ADM-' . date('Ymd') . '-' . strtoupper(uniqid(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2)));

         $order = Orders::create($validated);

         OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $order->order_status,
            'description' => 'Order created.',
            'location' => 'System',
            'tracked_at' => now(),
         ]);

         app(\App\Services\OrderDispatchService::class)->dispatchAfterOrderPlaced($order->fresh(['vendor', 'customer.user']));

         return redirect()->route('admin.orders')->with('success', 'Order created successfully.');
      } catch (\Exception $e) {
         return back()->withInput()->with('error', 'Error creating order: ' . $e->getMessage());
      }
   }

   public function editOrder($id)
   {
      $title = 'Edit Order';
      $order = Orders::find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $customers = DB::table('customers')
         ->leftJoin('users', 'customers.user_id', '=', 'users.user_id')
         ->select('customers.customer_id', 'users.name', 'users.email')
         ->where('users.status', 1)
         ->orderBy('users.name')
         ->get();

      // $vendors removed

      $activePaymentMethods = PaymentGateway::where('is_enabled', true)
         ->select('gateway', 'display_name')
         ->orderBy('id')
         ->get();

      return view('admin.orders.editOrder', compact('title', 'order', 'customers', 'activePaymentMethods'));
   }

   public function updateOrder(Request $request, $id)
   {
      $order = Orders::find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $validated = $request->validate([
         'customer_id' => 'required|integer',
         // 'vendor_id' removed
         'payment_method' => [
            'required',
            'string',
            'max:50',
            Rule::in(PaymentGateway::where('is_enabled', true)->pluck('gateway')->toArray()),
         ],
         'payment_status' => 'required|string|max:30',
         'order_status' => ['required', 'string', 'max:30', Rule::in(Orders::persistableOrderStatuses())],
         'subtotal' => 'nullable|numeric|min:0',
         'tax_amount' => 'nullable|numeric|min:0',
         'shipping_amount' => 'nullable|numeric|min:0',
         'total_amount' => 'required|numeric|min:0',
         'shipping_address' => 'nullable|string',
         'notes' => 'nullable|string',
      ]);

      $isActiveCustomer = DB::table('customers')
         ->leftJoin('users', 'customers.user_id', '=', 'users.user_id')
         ->where('customers.customer_id', $validated['customer_id'])
         ->where('users.status', 1)
         ->exists();

      if (!$isActiveCustomer) {
         return back()->withInput()->with('error', 'Only active customers can be selected for orders.');
      }

      // Vendor validation removed

      try {
         $previousStatus = $order->order_status;

         // Vendor order number update removed

         $order->update($validated);

         if ($previousStatus !== $order->order_status) {
            OrderTracking::create([
               'order_id' => $order->order_id,
               'status' => $order->order_status,
               'description' => 'Order updated and status changed from ' . ucfirst($previousStatus) . ' to ' . ucfirst($order->order_status) . '.',
               'location' => 'Admin Panel',
               'tracked_at' => now(),
            ]);
         }

         return redirect()->route('admin.orders')->with('success', 'Order updated successfully.');
      } catch (\Exception $e) {
         return back()->withInput()->with('error', 'Error updating order: ' . $e->getMessage());
      }
   }

   public function deleteOrder($id)
   {
      $order = Orders::find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      try {
         $order->delete();

         return redirect()->route('admin.orders')->with('success', 'Order deleted successfully.');
      } catch (\Exception $e) {
         return back()->with('error', 'Error deleting order: ' . $e->getMessage());
      }
    }

    public function orderDetails($id)
    {
      $title = 'Order Details';
      $order = $this->scopedOrdersQuery()->with(['customer.user', 'vendor', 'orderItems.product', 'deliveryAssignment.driver'])->find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $availableDrivers = Users::where('role_type', Users::DRIVER_APP_ROLE_TYPE)
         ->where('approval_status', 'approved')
         ->where('status', '1')
         ->orderBy('name')
         ->get(['user_id', 'name', 'mobile']);

      return view('admin.orders.orderDetails', compact('title', 'order', 'availableDrivers'));
    }

    public function orderTracking($id)
    {
      $title = 'Order Tracking';
      $order = $this->scopedOrdersQuery()->with(['customer.user', 'orderItems', 'trackings'])->find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      return view('admin.orders.orderTracking', compact('title', 'order'));
   }

   public function updateOrderStatus(Request $request, $id)
   {
      $order = $this->scopedOrdersQuery()->find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $validated = $request->validate([
         'order_status' => ['required', Rule::in(Orders::persistableOrderStatuses())],
         'payment_status' => 'nullable|string|max:30',
      ]);

      try {
         $previousStatus = $order->order_status;
         $order->update($validated);

         if ($previousStatus !== $order->order_status) {
            $actionDescriptions = [
               'accepted' => 'Order accepted.',
               'rejected' => 'Order rejected.',
               'out_for_delivery' => 'Order marked as out for delivery.',
               'shipped' => 'Order marked as shipped.',
               'delivered' => 'Order marked as delivered.',
               'processing' => 'Order moved to processing.',
               'cancelled' => 'Order cancelled.',
               'pending' => 'Order marked as pending.',
               'confirmed' => 'Order confirmed.',
               'payment_pending' => 'Order awaiting payment.',
               'completed' => 'Order completed.',
               'success' => 'Order completed.',
            ];

            $prevLabel = Orders::statusLabel((string) $previousStatus);
            $newLabel = Orders::statusLabel((string) $order->order_status);

            OrderTracking::create([
               'order_id' => $order->order_id,
               'status' => $order->order_status,
               'description' => $actionDescriptions[$order->order_status] ?? ('Order status changed from ' . $prevLabel . ' to ' . $newLabel . '.'),
               'location' => $this->isVendorPanel() ? 'Vendor Panel' : 'Admin Panel',
               'tracked_at' => now(),
            ]);
         }

         return back()->with('success', 'Order status updated successfully.');
      } catch (\Exception $e) {
         return back()->with('error', 'Error updating order status: ' . $e->getMessage());
      }
    }

    public function addOrderTracking(Request $request, $id)
    {
      $order = $this->scopedOrdersQuery()->find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $validated = $request->validate([
         'status' => ['required', 'string', 'max:30', Rule::in(Orders::persistableOrderStatuses())],
         'location' => 'nullable|string|max:255',
         'description' => 'nullable|string',
         'tracked_at' => 'nullable|date',
      ]);

      try {
         OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $validated['status'],
            'location' => $validated['location'] ?? null,
            'description' => $validated['description'] ?? null,
            'tracked_at' => $validated['tracked_at'] ?? now(),
         ]);

         $order->order_status = $validated['status'];
         $order->save();

         return back()->with('success', 'Tracking event added successfully.');
      } catch (\Exception $e) {
         return back()->with('error', 'Error adding tracking event: ' . $e->getMessage());
      }
    }

    public function downloadOrderInvoicePdf($id)
    {
      $order = Orders::with(['customer.user', 'orderItems'])->find($id);
      $storeSetting = StoreSetting::query()->first();

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $pdf = Pdf::loadView('admin.orders.orderInvoicePdf', [
         'order' => $order,
         'storeSetting' => $storeSetting,
      ])->setPaper('a4', 'portrait');

      $fileName = 'invoice-' . $order->order_number . '.pdf';

      return $pdf->download($fileName);
    }

    /**
     * Update delivery-related order status from the admin panel (no delivery partner).
     */
    public function updateDeliveryStatus(Request $request, $id)
    {
      $order = $this->scopedOrdersQuery()->find($id);

      if (!$order) {
         return back()->with('error', 'Order not found.');
      }

      $validated = $request->validate([
         'status' => 'required|in:shipped,out_for_delivery,delivered,processing',
         'notes' => 'nullable|string|max:500',
      ]);

      try {
         $order->order_status = $validated['status'];
         $order->save();

         $statusMessages = [
            'shipped' => 'Order marked as shipped.',
            'out_for_delivery' => 'Order marked as out for delivery.',
            'delivered' => 'Order marked as delivered.',
            'processing' => 'Order marked as ready to dispatch.',
         ];

         OrderTracking::create([
            'order_id' => $order->order_id,
            'status' => $order->order_status,
            'description' => ($statusMessages[$order->order_status] ?? 'Delivery status updated.')
                . (!empty($validated['notes']) ? ' Notes: ' . $validated['notes'] : ''),
            'location' => 'Admin Panel',
            'tracked_at' => now(),
         ]);

         return back()->with('success', 'Delivery status updated successfully.');
      } catch (\Exception $e) {
         return back()->with('error', 'Error updating delivery status: ' . $e->getMessage());
      }
   }

   private function applyOrdersExportFilters($query, Request $request): void
   {
      $search = trim((string) $request->query('search', ''));
      if ($search !== '') {
         $query->where(function ($q) use ($search) {
            $q->where('order_number', 'like', "%{$search}%")
               ->orWhereHas('customer.user', function ($u) use ($search) {
                  $u->where('name', 'like', "%{$search}%");
               })
               ->orWhere('payment_method', 'like', "%{$search}%");
         });
      }

      $dateFrom = $request->query('date_from', $request->query('from_date'));
      $dateTo = $request->query('date_to', $request->query('to_date'));
      if ($dateFrom) {
         $query->whereDate('created_at', '>=', $dateFrom);
      }
      if ($dateTo) {
         $query->whereDate('created_at', '<=', $dateTo);
      }

      $statusFilter = $request->query('status_filter');
      if ($statusFilter && isset(self::orderStatusGroups()[$statusFilter])) {
         $query->whereIn('order_status', self::orderStatusGroups()[$statusFilter]);
      }

      $paymentStatus = $request->query('payment_status');
      if ($paymentStatus && in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'], true)) {
         $query->where('payment_status', $paymentStatus);
      }

      if ($request->query('scope') === 'incoming') {
         $query->where('order_status', 'pending');
      }
   }

   public function exportOrdersExcel(Request $request)
   {
      $query = Orders::with(['customer.user'])->orderByDesc('order_id');
      $this->applyOrdersExportFilters($query, $request);

      $orders = $query->lazy(500); // fetch in chunks of 500 to avoid loading all into memory at once

      $fileName = 'orders-export-' . date('Y-m-d-H-i-s') . '.xls';

      $headers = [
         "Content-Type" => "application/vnd.ms-excel",
         "Content-Disposition" => "attachment; filename=\"$fileName\"",
         "Pragma" => "no-cache",
         "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
         "Expires" => "0",
      ];

      $content = "S.No.\tOrder ID\tCustomer Name\tOrder Date\tPayment Method\tPayment Status\tOrder Status\tAmount\n";

      foreach ($orders as $index => $order) {
         $content .= ($index + 1) . "\t";
         $content .= ($order->order_number ?? '') . "\t";
         $content .= (optional(optional($order->customer)->user)->name ?? 'Customer N/A') . "\t";
         $content .= ($order->created_at ? $order->created_at->format('d-m-Y') : '') . "\t";
         $content .= ucfirst($order->payment_method ?? '') . "\t";
         $content .= ucfirst($order->payment_status ?? '') . "\t";
         $content .= ucfirst(str_replace('_', ' ', $order->order_status ?? '')) . "\t";
         $content .= number_format((float) $order->total_amount, 2, '.', '') . "\n";
      }

      return response($content, 200, $headers);
   }

   public function exportOrdersPdf(Request $request)
   {
      $query = Orders::with(['customer.user'])->orderByDesc('order_id');
      $this->applyOrdersExportFilters($query, $request);

      $count = $query->count();

      if ($count > 500) {
         return redirect()->back()->with('error', 'PDF export is limited to 500 records. Please narrow your filters or use Excel export for larger datasets.');
      }

      $orders = $query->get();

      $storeSetting = StoreSetting::first();

      $pdf = Pdf::loadView('admin.orders.ordersExportPdf', compact('orders', 'storeSetting', 'fromDate', 'toDate', 'search'))
         ->setPaper('a4', 'landscape');

      $fileName = 'orders-export-' . date('Y-m-d-H-i-s') . '.pdf';

      return $pdf->download($fileName);
   }
}