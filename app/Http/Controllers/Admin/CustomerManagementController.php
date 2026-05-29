<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\Users;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\StoreSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CustomerManagementController extends Controller
{
    private function customerBaseQuery()
    {
        return Customers::join('users', 'customers.user_id', '=', 'users.user_id')
            ->where('users.role_type', Users::CUSTOMER_APP_ROLE_TYPE)
            ->where('users.status', '!=', 2);
    }

    private function customerAddressRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) {
                $normalizedValue = preg_replace('/\s+/', ' ', trim((string) $value));
                $chunks = preg_split('/\s+/', $normalizedValue, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($chunks as $chunk) {
                    if (mb_strlen($chunk) > 15) {
                        $fail('Address must not contain any word longer than 15 characters.');
                        return;
                    }
                }

                if (preg_match('/(.)\1{5,}/u', $normalizedValue)) {
                    $fail('Address must not repeat the same character more than 5 times in a row.');
                }
            },
        ];
    }

    private function customerUserSelectColumns(): array
    {
        $cols = [
            'users.name',
            'users.email',
            'users.mobile',
            'users.status',
            'users.user_type',
            'users.profile_image',
        ];
        if (Schema::hasColumn('users', 'gst_number')) {
            $cols[] = 'users.gst_number';
        }
        if (Schema::hasColumn('users', 'approval_status')) {
            $cols[] = 'users.approval_status';
        }
        if (Schema::hasColumn('users', 'gst_verified_at')) {
            $cols[] = 'users.gst_verified_at';
        }
        if (Schema::hasColumn('users', 'created_at')) {
            $cols[] = 'users.created_at as registration_date';
        }

        return array_merge(['customers.*'], $cols);
    }

    private function resolveCustomerUser(int $customerId): ?Users
    {
        $customer = Customers::where('customer_id', $customerId)->first();
        if (!$customer) {
            return null;
        }

        return Users::where('user_id', $customer->user_id)
            ->where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)
            ->where('status', '!=', 2)
            ->first();
    }

    public function allCustomers(Request $request)
    {
        $title = 'Customer Management';

        $hasApproval = Schema::hasColumn('users', 'approval_status');

        $search = trim((string) $request->query('search', ''));

        $base = $this->customerBaseQuery();

        $totalCustomers = (clone $base)->count();
        if ($hasApproval) {
            $pendingCount = (clone $base)->where('users.approval_status', 'pending')->count();
            $rejectedCount = (clone $base)->where('users.approval_status', 'rejected')->count();
            $activeCount = (clone $base)->where('users.approval_status', 'approved')->where('users.status', 1)->count();
            $onHoldCount = (clone $base)->where('users.approval_status', 'approved')->where('users.status', 0)->count();
        } else {
            $pendingCount = 0;
            $rejectedCount = 0;
            $activeCount = (clone $base)->where('users.status', 1)->count();
            $onHoldCount = (clone $base)->where('users.status', 0)->count();
        }

        $listQuery = $this->customerBaseQuery();

        if ($search !== '') {
            $escapedSearch = addcslashes($search, '%_\\');
            $listQuery->where(function ($q) use ($escapedSearch) {
                $q->where('users.name', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('users.email', 'like', '%' . $escapedSearch . '%')
                  ->orWhere('users.mobile', 'like', '%' . $escapedSearch . '%');
            });
        }

        $statusFilter = $request->query('status', 'all');
        if ($statusFilter === 'active') {
            $listQuery->where('users.status', 1);
        } elseif ($statusFilter === 'inactive') {
            $listQuery->where('users.status', 0);
        }

        $allCustomers = $listQuery
            ->select($this->customerUserSelectColumns())
            ->orderByDesc('customers.customer_id')
            ->paginate(10)
            ->withQueryString();

        $customerIds = $allCustomers->getCollection()->pluck('customer_id')->all();

        $orderCounts = [];
        if (!empty($customerIds) && Schema::hasTable('orders')) {
            $orderCounts = Orders::query()
                ->select('customer_id', DB::raw('COUNT(*) as total'))
                ->whereIn('customer_id', $customerIds)
                ->groupBy('customer_id')
                ->pluck('total', 'customer_id')
                ->all();
        }

        $activitySummary = DB::table('orders')
            ->leftJoin('order_trackings', 'orders.order_id', '=', 'order_trackings.order_id')
            ->select(
                'orders.customer_id',
                DB::raw('MAX(COALESCE(order_trackings.tracked_at, orders.created_at)) as last_activity_at')
            )
            ->when(!empty($customerIds), function ($query) use ($customerIds) {
                $query->whereIn('orders.customer_id', $customerIds);
            })
            ->groupBy('orders.customer_id')
            ->get()
            ->keyBy('customer_id');

        $mappedCustomers = $allCustomers->getCollection()->map(function ($customer) use ($activitySummary) {
            $summary = $activitySummary->get($customer->customer_id);
            $customer->last_activity_at = $summary->last_activity_at ?? null;

            return $customer;
        });

        $allCustomers->setCollection($mappedCustomers);

        $hasGstVerified = Schema::hasColumn('users', 'gst_verified_at');

        $modalEditCustomer = null;
        if ($request->query('open') === 'edit' && $request->filled('id')) {
            try {
                $editId = Crypt::decrypt(urldecode((string) $request->query('id')));
                $modalEditCustomer = $this->customerBaseQuery()
                    ->where('customers.customer_id', $editId)
                    ->select($this->customerUserSelectColumns())
                    ->first();
            } catch (\Exception $e) {
                $modalEditCustomer = null;
            }
        }

        return view('admin.customers.customersList', compact(
            'title',
            'allCustomers',
            'totalCustomers',
            'activeCount',
            'pendingCount',
            'rejectedCount',
            'onHoldCount',
            'hasApproval',
            'hasGstVerified',
            'search',
            'statusFilter',
            'orderCounts',
            'modalEditCustomer',
        ));
    }

    public function addCustomer()
    {
        return redirect()->route('admin.customers', ['open' => 'add']);
    }

    public function storeCustomer(Request $request)
    {
        try {
            $validated = $request->validate($this->figmaUserFormRules(), $this->figmaUserFormMessages());
        } catch (ValidationException $e) {
            return redirect()->route('admin.customers')
                ->withInput()
                ->with('open_user_modal', 'add')
                ->withErrors($e->errors());
        }

        $plainPassword = $this->generateCustomerAppPassword();

        DB::transaction(function () use ($validated, $plainPassword) {
            $user = new Users();
            $user->name = $validated['customer_name'];
            $user->email = $validated['customer_email'];
            $user->mobile = $validated['customer_phone'];
            $user->password = bcrypt($plainPassword);
            $user->role_type = Users::CUSTOMER_APP_ROLE_TYPE;
            $user->status = 1;
            if (Schema::hasColumn('users', 'approval_status')) {
                $user->approval_status = 'approved';
            }
            if (Schema::hasColumn('users', 'user_type')) {
                $user->user_type = 'Retailer';
            }
            $user->save();

            $customer = new Customers();
            $customer->user_id = $user->user_id;
            $customer->customer_address = $validated['customer_address'];
            $customer->save();
        });

        return redirect()->route('admin.customers')->with('success', 'User created successfully.');
    }

    public function editCustomer($id)
    {
        try {
            Crypt::decrypt(urldecode($id));
        } catch (\Exception $e) {
            return redirect()->route('admin.customers')->with('error', 'Invalid user link.');
        }

        return redirect()->route('admin.customers', ['open' => 'edit', 'id' => $id]);
    }

    public function viewCustomer($id)
    {
        $title = 'Customer Details';

        try {
            $customerId = Crypt::decrypt(urldecode($id));
        } catch (\Exception $e) {
            return redirect()->route('admin.customers')->with('error', 'Invalid customer link.');
        }

        $customer = Customers::join('users', 'customers.user_id', '=', 'users.user_id')
            ->where('customers.customer_id', $customerId)
            ->where('users.role_type', Users::CUSTOMER_APP_ROLE_TYPE)
            ->where('users.status', '!=', 2)
            ->select($this->customerUserSelectColumns())
            ->first();

        if (!$customer) {
            return redirect()->route('admin.customers')->with('error', 'Customer not found.');
        }

        $activitySummary = DB::table('orders')
            ->where('customer_id', $customer->customer_id)
            ->select(
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('COALESCE(SUM(total_amount), 0) as total_spent'),
                DB::raw("SUM(CASE WHEN LOWER(order_status) IN ('delivered', 'completed', 'success') THEN 1 ELSE 0 END) as completed_orders"),
                DB::raw("SUM(CASE WHEN LOWER(order_status) IN ('pending', 'processing', 'confirmed') THEN 1 ELSE 0 END) as pending_orders"),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->first();

        $orderActivities = DB::table('orders')
            ->where('customer_id', $customer->customer_id)
            ->select(
                'order_id',
                'order_number',
                'order_status as status',
                'total_amount',
                'created_at as activity_at',
                DB::raw("'order' as activity_type"),
                DB::raw("'Order placed' as activity_label"),
                DB::raw('NULL as description')
            )
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $trackingActivities = DB::table('order_trackings')
            ->join('orders', 'order_trackings.order_id', '=', 'orders.order_id')
            ->where('orders.customer_id', $customer->customer_id)
            ->select(
                'orders.order_id',
                'orders.order_number',
                'order_trackings.status',
                'orders.total_amount',
                'order_trackings.tracked_at as activity_at',
                DB::raw("'tracking' as activity_type"),
                DB::raw("'Order update' as activity_label"),
                'order_trackings.description'
            )
            ->orderByDesc('order_trackings.tracked_at')
            ->limit(10)
            ->get();

        $recentActivities = $orderActivities
            ->concat($trackingActivities)
            ->sortByDesc('activity_at')
            ->take(8)
            ->values();

        $hasApproval = Schema::hasColumn('users', 'approval_status');
        $hasGstVerified = Schema::hasColumn('users', 'gst_verified_at');
        $customerAddresses = CustomerAddress::where('customer_id', $customer->customer_id)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->orderByDesc('customer_address_id')
            ->get();

        return view('admin.customers.viewCustomer', compact(
            'title',
            'customer',
            'customerAddresses',
            'activitySummary',
            'recentActivities',
            'hasApproval',
            'hasGstVerified'
        ));
    }

    public function updateCustomer(Request $request)
    {
        $customerId = (int) $request->input('customer_id');
        $user = $this->resolveCustomerUser($customerId);

        try {
            $validated = $request->validate(
                array_merge(
                    ['customer_id' => ['required', 'integer', 'exists:customers,customer_id']],
                    $this->figmaUserFormRules($user?->user_id)
                ),
                $this->figmaUserFormMessages()
            );
        } catch (ValidationException $e) {
            return redirect()->route('admin.customers')
                ->withInput()
                ->with('open_user_modal', 'edit')
                ->withErrors($e->errors());
        }

        $customer = Customers::find($validated['customer_id']);
        if (!$customer) {
            return redirect()->route('admin.customers')->with('error', 'User not found.');
        }

        $user = Users::find($customer->user_id);
        if (!$user) {
            return redirect()->route('admin.customers')->with('error', 'User account not found.');
        }

        $user->name = $validated['customer_name'];
        $user->email = $validated['customer_email'];
        $user->mobile = $validated['customer_phone'];
        $user->save();

        $customer->customer_address = $validated['customer_address'];
        $customer->save();

        return redirect()->route('admin.customers')->with('success', 'User updated successfully.');
    }

    private function figmaUserFormRules(?int $ignoreUserId = null): array
    {
        $emailRule = Rule::unique('users', 'email');
        $phoneRule = Rule::unique('users', 'mobile');
        if ($ignoreUserId) {
            $emailRule = $emailRule->ignore($ignoreUserId, 'user_id');
            $phoneRule = $phoneRule->ignore($ignoreUserId, 'user_id');
        }

        return [
            'customer_name' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z ]+$/'],
            'customer_email' => ['required', 'email', 'max:120', $emailRule],
            'customer_phone' => ['required', 'digits:10', $phoneRule],
            'customer_address' => $this->customerAddressRules(),
        ];
    }

    private function figmaUserFormMessages(): array
    {
        return [
            'customer_name.required' => 'Full name is required.',
            'customer_name.regex' => 'Full name may only contain letters and spaces.',
            'customer_email.required' => 'Email address is required.',
            'customer_email.email' => 'Enter a valid email address.',
            'customer_email.unique' => 'This email is already registered.',
            'customer_phone.required' => 'Phone number is required.',
            'customer_phone.digits' => 'Phone number must be exactly 10 digits.',
            'customer_phone.unique' => 'This phone number is already registered.',
            'customer_address.required' => 'Address is required.',
        ];
    }

    private function generateCustomerAppPassword(): string
    {
        return 'MoA@' . Str::upper(Str::random(4)) . random_int(10, 99) . '!';
    }

    public function deleteCustomer($id)
    {
        try {
            $customerId = Crypt::decrypt(urldecode($id));
        } catch (\Exception $e) {
            return redirect()->route('admin.customers')->with('error', 'Invalid customer link.');
        }

        $customer = Customers::where('customer_id', $customerId)->first();
        if (!$customer) {
            return redirect()->route('admin.customers')->with('error', 'Customer not found.');
        }

        $user = Users::where('user_id', $customer->user_id)->first();
        if (!$user) {
            return redirect()->route('admin.customers')->with('error', 'Customer user not found.');
        }

        $user->status = 2;
        $user->save();

        return redirect()->route('admin.customers')
            ->with('success', 'Customer Deleted Successfully');
    }

    public function toggleStatus($id)
    {
        $customer = Customers::where('customer_id', $id)->first();
        if (!$customer) {
            return back()->with('error', 'Customer not found.');
        }

        $user = Users::where('user_id', $customer->user_id)
            ->where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)
            ->where('status', '!=', 2)
            ->first();

        if (!$user) {
            return back()->with('error', 'Customer user not found.');
        }

        if (Schema::hasColumn('users', 'approval_status')) {
            $approval = strtolower((string) ($user->approval_status ?? 'approved'));
            if ($approval !== 'approved') {
                return back()->with('error', 'Activate or deactivate is only available after registration is approved.');
            }
        }

        $user->status = (int) $user->status === 1 ? 0 : 1;
        $user->save();

        return back()->with('success', 'Customer status updated successfully.');
    }

    public function approveRegistration(int $id)
    {
        $user = $this->resolveCustomerUser($id);
        if (!$user) {
            return back()->with('error', 'Customer not found.');
        }

        if (!Schema::hasColumn('users', 'approval_status')) {
            return back()->with('error', 'Run database migrations to enable registration approval.');
        }

        if (strtolower((string) $user->approval_status) !== 'pending') {
            return back()->with('error', 'Only pending registrations can be approved.');
        }

        $user->approval_status = 'approved';
        $user->status = 1;
        $user->save();

        return back()->with('success', 'Customer registration approved and account activated.');
    }

    public function rejectRegistration(Request $request, int $id)
    {
        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $this->resolveCustomerUser($id);
        if (!$user) {
            return back()->with('error', 'Customer not found.');
        }

        if (!Schema::hasColumn('users', 'approval_status')) {
            return back()->with('error', 'Run database migrations to enable registration rejection.');
        }

        if (strtolower((string) $user->approval_status) !== 'pending') {
            return back()->with('error', 'Only pending registrations can be rejected.');
        }

        $user->approval_status = 'rejected';
        $user->status = 0;
        if (Schema::hasColumn('users', 'gst_verified_at')) {
            $user->gst_verified_at = null;
        }
        $user->save();

        $reason = trim((string) $request->input('reason', ''));
        $msg = 'Registration rejected.';
        if ($reason !== '') {
            $msg .= ' ' . $reason;
        }

        return back()->with('success', $msg);
    }

    public function verifyGst(int $id)
    {
        $user = $this->resolveCustomerUser($id);
        if (!$user) {
            return back()->with('error', 'Customer not found.');
        }

        if (!Schema::hasColumn('users', 'gst_verified_at')) {
            return back()->with('error', 'Run database migrations to enable GST verification.');
        }

        $gst = trim((string) ($user->gst_number ?? ''));
        if ($gst === '') {
            return back()->with('error', 'This customer has no GST number on file.');
        }

        $user->gst_verified_at = now();
        $user->save();

        return back()->with('success', 'GST details marked as verified.');
    }

    public function exportCustomersExcel(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = $this->customerBaseQuery()
            ->select($this->customerUserSelectColumns())
            ->orderByDesc('customers.customer_id');

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('users.name', 'like', '%' . $escaped . '%')
                  ->orWhere('users.email', 'like', '%' . $escaped . '%')
                  ->orWhere('users.mobile', 'like', '%' . $escaped . '%');
            });
        }

        $customers = $query->get();
        $hasApproval = Schema::hasColumn('users', 'approval_status');

        $fileName = 'customers-export-' . date('Y-m-d-H-i-s') . '.xls';
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($customers, $hasApproval) {
            $header = "S.No.\tName\tEmail\tMobile\tUser Type\tAccount Status";
            if ($hasApproval) {
                $header .= "\tApproval";
            }
            $header .= "\tRegistered On\n";
            echo $header;

            foreach ($customers as $index => $customer) {
                $line = ($index + 1) . "\t";
                $line .= ($customer->name ?? 'N/A') . "\t";
                $line .= ($customer->email ?? '') . "\t";
                $line .= ($customer->mobile ?? '-') . "\t";
                $line .= ($customer->user_type ?? '-') . "\t";
                $line .= ((int)($customer->status ?? 0) === 1 ? 'Active' : 'Inactive') . "\t";
                if ($hasApproval) {
                    $line .= ucfirst((string)($customer->approval_status ?? 'approved')) . "\t";
                }
                $regDate = !empty($customer->registration_date)
                    ? \Carbon\Carbon::parse($customer->registration_date)->format('d-m-Y')
                    : '-';
                $line .= $regDate . "\n";
                echo $line;
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportCustomersPdf(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $query = $this->customerBaseQuery()
            ->select($this->customerUserSelectColumns())
            ->orderByDesc('customers.customer_id');

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('users.name', 'like', '%' . $escaped . '%')
                  ->orWhere('users.email', 'like', '%' . $escaped . '%')
                  ->orWhere('users.mobile', 'like', '%' . $escaped . '%');
            });
        }

        $customers = $query->get();
        $hasApproval = Schema::hasColumn('users', 'approval_status');
        $storeSetting = StoreSetting::first();

        $pdf = Pdf::loadView('admin.customers.customersExportPdf', compact('customers', 'storeSetting', 'search', 'hasApproval'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('customers-export-' . date('Y-m-d-H-i-s') . '.pdf');
    }
}
