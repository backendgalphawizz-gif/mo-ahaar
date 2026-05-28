<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class VendorManagementController extends Controller
{
    public function registerForm()
    {
        return view('auth.vendor-register');
    }

    public function registerSubmit(Request $request)
    {
        $validated = $this->validateVendor($request, null, true);

        DB::beginTransaction();
        try {
            $user = Users::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => Hash::make($validated['password']),
                'role_type' => 3,
                'status' => '0',
            ]);

            $vendorData = $this->mapVendorPayload($request, $validated);
            $vendorData['user_id'] = $user->user_id;
            $vendorData['vendor_code'] = Vendor::generateVendorCode();
            $vendorData['approval_status'] = 'pending';
            $vendorData['status'] = '0';

            Vendor::create($vendorData);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to submit registration: ' . $e->getMessage());
        }

        return redirect()->route('vendor.login')->with('success', 'Registration submitted successfully. Please wait for admin approval.');
    }

    private function uploadFile(Request $request, string $field, string $directory = 'vendors'): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = public_path('uploads/' . $directory);
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        $file->move($path, $filename);

        return $filename;
    }

    private function vendorListQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', 'all'));

        $query = Vendor::query()->orderByDesc('vendor_id');

        if ($status !== '' && $status !== 'all') {
            $query->where('approval_status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', '%' . $search . '%')
                    ->orWhere('owner_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhere('vendor_code', 'like', '%' . $search . '%');
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $vendors = $this->vendorListQuery($request)->get();

        $vendorIds = $vendors->pluck('vendor_id')->filter()->all();
        $productCounts = [];
        $orderCounts = [];

        if (!empty($vendorIds) && Schema::hasColumn('products', 'vendor_id')) {
            $productCounts = Product::select('vendor_id', DB::raw('COUNT(*) as total'))
                ->whereIn('vendor_id', $vendorIds)
                ->groupBy('vendor_id')
                ->pluck('total', 'vendor_id')
                ->all();
        }

        if (!empty($vendorIds)) {
            $orderCounts = Orders::select('vendor_id', DB::raw('COUNT(*) as total'))
                ->whereIn('vendor_id', $vendorIds)
                ->groupBy('vendor_id')
                ->pluck('total', 'vendor_id')
                ->all();
        }

        return view('admin.vendors.vendorsList', [
            'title' => 'Vendor Management',
            'vendors' => $vendors,
            'productCounts' => $productCounts,
            'orderCounts' => $orderCounts,
            'search' => $request->query('search', ''),
            'status' => $request->query('status', 'all'),
        ]);
    }

    public function addVendor()
    {
        return view('admin.vendors.addVendor', [
            'title' => 'Add New Vendor',
            'vendor' => null,
            'tab' => request('tab', 'personal'),
        ]);
    }

    public function storeVendor(Request $request)
    {
        $validated = $this->validateVendor($request, null, true);

        DB::beginTransaction();
        try {
            $user = Users::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'mobile' => $validated['mobile'],
                'password' => Hash::make($validated['password']),
                'role_type' => 3,
                'status' => '1',
            ]);

            $vendorData = $this->mapVendorPayload($request, $validated);
            $vendorData['user_id'] = $user->user_id;
            $vendorData['vendor_code'] = Vendor::generateVendorCode();
            $vendorData['approval_status'] = $validated['approval_status'] ?? 'pending';
            $vendorData['status'] = '1';

            Vendor::create($vendorData);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create vendor: ' . $e->getMessage());
        }

        return redirect()->route('admin.vendors')->with('success', 'Vendor created successfully.');
    }

    public function viewVendor($id, Request $request)
    {
        $vendor = Vendor::findOrFail($id);
        $tab = $request->query('tab', 'details');

        $products = collect();
        $orders = collect();
        $reviews = collect();

        if ($tab === 'food' && Schema::hasColumn('products', 'vendor_id')) {
            $products = Product::where('vendor_id', $vendor->vendor_id)->orderByDesc('product_id')->get();
        }

        if ($tab === 'orders') {
            $orders = Orders::with(['customer.user'])
                ->where('vendor_id', $vendor->vendor_id)
                ->orderByDesc('order_id')
                ->get();
        }

        if ($tab === 'reviews' && Schema::hasTable('product_reviews') && Schema::hasColumn('products', 'vendor_id')) {
            $reviews = ProductReview::query()
                ->join('products', 'products.product_id', '=', 'product_reviews.product_id')
                ->leftJoin('customers', 'customers.customer_id', '=', 'product_reviews.customer_id')
                ->leftJoin('users', 'users.user_id', '=', 'customers.user_id')
                ->where('products.vendor_id', $vendor->vendor_id)
                ->select(
                    'product_reviews.*',
                    'products.product_name',
                    'users.name as customer_name'
                )
                ->orderByDesc('product_reviews.review_id')
                ->get();
        }

        return view('admin.vendors.viewVendor', compact('vendor', 'tab', 'products', 'orders', 'reviews'));
    }

    public function editVendor($id)
    {
        $vendor = Vendor::findOrFail($id);

        return view('admin.vendors.editVendor', [
            'title' => 'Edit Vendor',
            'vendor' => $vendor,
            'tab' => request('tab', 'personal'),
        ]);
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $validated = $this->validateVendor($request, $vendor, false);

        DB::beginTransaction();
        try {
            if ($vendor->user_id) {
                Users::where('user_id', $vendor->user_id)->update([
                    'name' => $validated['owner_name'],
                    'email' => $validated['email'],
                    'mobile' => $validated['mobile'],
                ]);
            }

            $vendor->update($this->mapVendorPayload($request, $validated));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update vendor: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $request->input('tab', 'personal')])
            ->with('success', 'Vendor updated successfully.');
    }

    public function updateApprovalStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'suspended', 'rejected'])],
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->approval_status = $validated['approval_status'];
        if ($validated['approval_status'] === 'approved') {
            $vendor->status = '1';
        } elseif (in_array($validated['approval_status'], ['suspended', 'rejected'], true)) {
            $vendor->status = '0';
        }
        $vendor->save();

        if ($vendor->user_id) {
            Users::where('user_id', $vendor->user_id)->update([
                'status' => $validated['approval_status'] === 'approved' ? '1' : '0',
            ]);
        }

        return back()->with('success', 'Vendor status updated to ' . ucfirst($validated['approval_status']) . '.');
    }

    public function updateCommission(Request $request, $id)
    {
        $validated = $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        $vendor = Vendor::findOrFail($id);
        $vendor->commission_percent = round((float) $validated['commission_percent'], 2);
        $vendor->save();

        return back()->with('success', 'Commission updated successfully.');
    }

    public function toggleBlock($id)
    {
        $vendor = Vendor::findOrFail($id);
        $vendor->approval_status = $vendor->approval_status === 'suspended' ? 'approved' : 'suspended';
        $vendor->status = $vendor->approval_status === 'approved' ? '1' : '0';
        $vendor->save();

        return back()->with('success', 'Vendor block status updated.');
    }

    public function deleteVendor($id)
    {
        $vendor = Vendor::findOrFail($id);
        if ($vendor->user_id) {
            Users::where('user_id', $vendor->user_id)->update(['status' => '2']);
        }
        $vendor->delete();

        return redirect()->route('admin.vendors')->with('success', 'Vendor deleted successfully.');
    }

    public function exportVendorsExcel(Request $request)
    {
        $vendors = $this->vendorListQuery($request)->get();
        $fileName = 'vendors-' . date('Y-m-d-H-i-s') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($vendors) {
            echo "Sl No.\tShop Name\tVendor Name\tEmail\tMobile\tStatus\n";
            foreach ($vendors as $index => $vendor) {
                echo ($index + 1) . "\t";
                echo ($vendor->business_name ?? '') . "\t";
                echo ($vendor->owner_name ?? '') . "\t";
                echo ($vendor->email ?? '') . "\t";
                echo ($vendor->mobile ?? '') . "\t";
                echo ucfirst((string) ($vendor->approval_status ?? '')) . "\n";
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function validateVendor(Request $request, ?Vendor $vendor, bool $isCreate): array
    {
        $rules = [
            'owner_name' => 'required|string|max:100',
            'mobile' => 'required|string|max:15',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($vendor?->user_id, 'user_id'),
            ],
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,others',
            'business_name' => 'required|string|max:150',
            'business_email' => 'nullable|email|max:255',
            'business_phone' => 'nullable|string|max:20',
            'business_description' => 'nullable|string|max:2000',
            'tax_name' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:15',
            'address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'bank_name' => 'nullable|string|max:150',
            'branch_name' => 'nullable|string|max:150',
            'bank_account' => 'nullable|string|max:30',
            'account_holder_name' => 'nullable|string|max:150',
            'ifsc_code' => 'nullable|string|max:11',
            'account_type' => 'nullable|string|max:50',
            'commission_percent' => 'nullable|numeric|min:0|max:100',
            'approval_status' => 'nullable|in:pending,approved,suspended,rejected',
            'profile_image' => 'nullable|image|max:2048',
            'business_logo' => 'nullable|image|max:2048',
            'business_banner' => 'nullable|image|max:4096',
            'shop_image' => 'nullable|image|max:4096',
            'aadhaar_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'pan_card' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'gst_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'food_license_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'bank_passbook_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'address_proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'national_identity_card_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ];

        if ($isCreate) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        return $request->validate($rules);
    }

    protected function mapVendorPayload(Request $request, array $validated): array
    {
        $data = collect($validated)->only([
            'owner_name', 'mobile', 'email', 'dob', 'gender',
            'business_name', 'business_email', 'business_phone', 'business_description',
            'tax_name', 'tax_number', 'pan_number', 'gst_number', 'address', 'latitude', 'longitude',
            'bank_name', 'branch_name', 'bank_account', 'account_holder_name',
            'ifsc_code', 'account_type', 'commission_percent', 'approval_status',
        ])->toArray();

        $fileMap = [
            'profile_image' => 'vendors',
            'business_logo' => 'vendors',
            'business_banner' => 'vendors',
            'shop_image' => 'vendors',
            'aadhaar_card' => 'vendors/documents',
            'pan_card' => 'vendors/documents',
            'gst_file' => 'vendors/documents',
            'food_license_file' => 'vendors/documents',
            'bank_passbook_file' => 'vendors/documents',
            'address_proof_file' => 'vendors/documents',
            'national_identity_card_file' => 'vendors/documents',
        ];

        foreach ($fileMap as $field => $dir) {
            $uploaded = $this->uploadFile($request, $field, $dir);
            if ($uploaded) {
                $data[$field] = $uploaded;
            }
        }

        return $data;
    }
}
