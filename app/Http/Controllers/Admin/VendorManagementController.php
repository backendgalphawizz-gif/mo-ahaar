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
use Illuminate\Validation\ValidationException;
use App\Support\VendorFormValidator;

class VendorManagementController extends Controller
{
    public function registerForm()
    {
        return view('auth.vendor-register');
    }

    public function registerSubmit(Request $request)
    {
        $this->normalizeVendorRequest($request);
        $validated = VendorFormValidator::validateComplete($request, null, true);

        DB::beginTransaction();
        try {
            $user = Users::create([
                'name' => $validated['owner_name'],
                'email' => $this->resolveVendorEmail($validated),
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

        $dateFrom = trim((string) $request->query('date_from', ''));
        if ($dateFrom !== '' && Schema::hasColumn('vendors', 'created_at')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $vendors = $this->vendorListQuery($request)->get();

        $vendorIds = $vendors->pluck('vendor_id')->filter()->all();
        $productCounts = [];
        $categoryCounts = [];
        $orderCounts = [];

        if (!empty($vendorIds) && Schema::hasColumn('products', 'vendor_id')) {
            $productCounts = Product::select('vendor_id', DB::raw('COUNT(*) as total'))
                ->whereIn('vendor_id', $vendorIds)
                ->groupBy('vendor_id')
                ->pluck('total', 'vendor_id')
                ->all();

            if (Schema::hasColumn('products', 'category_id')) {
                $categoryCounts = Product::select('vendor_id', DB::raw('COUNT(DISTINCT category_id) as total'))
                    ->whereIn('vendor_id', $vendorIds)
                    ->whereNotNull('category_id')
                    ->groupBy('vendor_id')
                    ->pluck('total', 'vendor_id')
                    ->all();
            }
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
            'categoryCounts' => $categoryCounts,
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
            'tab' => old('tab', request('tab', 'personal')),
            'wizard' => session('admin.vendor_wizard.create', []),
        ]);
    }

    public function storeVendor(Request $request)
    {
        $tab = (string) $request->input('tab', 'personal');
        $action = (string) $request->input('wizard_action', 'submit');

        if ($action === 'next') {
            return $this->advanceVendorWizard($request, true, null, $tab);
        }

        $wizard = session('admin.vendor_wizard.create', []);
        $this->normalizeVendorRequest($request);
        $this->mergeWizardIntoRequest($request, $wizard);

        try {
            $validated = VendorFormValidator::validateComplete($request, null, true);
        } catch (ValidationException $e) {
            return $this->redirectVendorWizardWithErrors($e, true, $request);
        }

        $merged = array_merge($wizard, $validated);
        $merged = $this->mergeWizardFileUploads($request, 'documents', $merged);
        $merged = $this->mergeWizardFileUploads($request, 'personal', $merged);

        DB::beginTransaction();
        try {
            $user = Users::create([
                'name' => $merged['owner_name'],
                'email' => $this->resolveVendorEmail($merged),
                'mobile' => $merged['mobile'],
                'password' => Hash::make($merged['password']),
                'role_type' => 3,
                'status' => '1',
            ]);

            $vendorData = $this->mapVendorPayload($request, $merged);
            $vendorData['user_id'] = $user->user_id;
            $vendorData['vendor_code'] = Vendor::generateVendorCode();
            $vendorData['approval_status'] = $merged['approval_status'] ?? 'pending';
            $vendorData['status'] = '1';

            Vendor::create($vendorData);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to create vendor: ' . $e->getMessage());
        }

        session()->forget('admin.vendor_wizard.create');

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
            'tab' => old('tab', request('tab', 'personal')),
            'wizard' => [],
        ]);
    }

    public function updateVendor(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);
        $tab = (string) $request->input('tab', 'personal');
        $action = (string) $request->input('wizard_action', 'submit');

        if ($action === 'next') {
            return $this->advanceVendorWizard($request, false, $vendor, $tab);
        }

        if ($action === 'back') {
            try {
                $this->persistVendorWizardTab($request, $vendor, $tab);
            } catch (ValidationException $e) {
                return $this->redirectVendorWizardWithErrors($e, false, $request, $vendor);
            } catch (\Throwable $e) {
                return back()->withInput()->with('error', 'Failed to update vendor: ' . $e->getMessage());
            }

            $prevTab = VendorFormValidator::prevTab($tab) ?? 'personal';

            return redirect()
                ->route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $prevTab])
                ->with('success', 'Changes saved successfully.');
        }

        DB::beginTransaction();
        try {
            $this->persistVendorWizardTab($request, $vendor, $tab);
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollBack();

            return $this->redirectVendorWizardWithErrors($e, false, $request, $vendor);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to update vendor: ' . $e->getMessage());
        }

        if ($tab === 'documents') {
            return redirect()
                ->route('admin.vendors')
                ->with('success', 'Restaurant updated successfully.');
        }

        return redirect()
            ->route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $tab])
            ->with('success', 'Changes saved successfully.');
    }

    private function redirectVendorWizardWithErrors(
        ValidationException $e,
        bool $isCreate,
        Request $request,
        ?Vendor $vendor = null
    ) {
        $tab = VendorFormValidator::tabForFirstError($e->validator->errors()->keys());
        $except = array_keys($request->allFiles());

        if ($isCreate) {
            return redirect()
                ->route('admin.add-vendor', ['tab' => $tab])
                ->withErrors($e->validator)
                ->withInput($request->except(array_merge($except, ['password', 'password_confirmation'])));
        }

        return redirect()
            ->route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $tab])
            ->withErrors($e->validator)
            ->withInput($request->except($except));
    }

    private function normalizeVendorRequest(Request $request): void
    {
        foreach (['mobile', 'business_phone'] as $phoneField) {
            if ($request->has($phoneField)) {
                $digits = preg_replace('/\D/', '', (string) $request->input($phoneField));
                $request->merge([$phoneField => $digits !== '' ? substr($digits, 0, 10) : '']);
            }
        }

        foreach (['pan_number', 'gst_number', 'ifsc_code'] as $field) {
            if ($request->filled($field)) {
                $request->merge([$field => strtoupper(trim((string) $request->input($field)))]);
            }
        }

        if ($request->has('bank_account')) {
            $account = preg_replace('/\D/', '', (string) $request->input('bank_account'));
            $request->merge(['bank_account' => $account !== '' ? substr($account, 0, 18) : '']);
        }

        if ($request->filled('account_type')) {
            $type = strtolower(trim((string) $request->input('account_type')));
            if ($type === 'saving') {
                $type = 'savings';
            }
            $request->merge(['account_type' => $type]);
        }
    }

    private function advanceVendorWizard(Request $request, bool $isCreate, ?Vendor $vendor, string $tab)
    {
        if (!in_array($tab, VendorFormValidator::TABS, true)) {
            $tab = 'personal';
        }

        if ($isCreate) {
            $this->normalizeVendorRequest($request);
            $validated = VendorFormValidator::validateTab($request, $tab, $vendor, true);

            $wizard = array_merge(session('admin.vendor_wizard.create', []), $validated);
            $wizard = $this->mergeWizardFileUploads($request, $tab, $wizard);
            session(['admin.vendor_wizard.create' => $wizard]);

            $nextTab = VendorFormValidator::nextTab($tab);
            if (!$nextTab) {
                return redirect()->route('admin.add-vendor', ['tab' => 'documents']);
            }

            return redirect()
                ->route('admin.add-vendor', ['tab' => $nextTab])
                ->with('success', 'Step saved. Continue with the next section.');
        }

        DB::beginTransaction();
        try {
            $this->persistVendorWizardTab($request, $vendor, $tab);
            DB::commit();
        } catch (ValidationException $e) {
            DB::rollBack();

            return $this->redirectVendorWizardWithErrors($e, false, $request, $vendor);
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('error', 'Failed to save step: ' . $e->getMessage());
        }

        $nextTab = VendorFormValidator::nextTab($tab);

        return redirect()
            ->route('admin.edit-vendor', ['id' => $vendor->vendor_id, 'tab' => $nextTab ?? $tab])
            ->with('success', 'Step saved. Continue with the next section.');
    }

    /**
     * Validate and persist a single wizard tab for an existing vendor.
     */
    private function persistVendorWizardTab(Request $request, Vendor $vendor, string $tab): void
    {
        if (!in_array($tab, VendorFormValidator::TABS, true)) {
            $tab = 'personal';
        }

        $this->normalizeVendorRequest($request);
        $validated = VendorFormValidator::validateTab($request, $tab, $vendor, false);

        if ($tab === 'personal' && $vendor->user_id) {
            Users::where('user_id', $vendor->user_id)->update([
                'name' => $validated['owner_name'],
                'email' => $this->resolveVendorEmail($validated),
                'mobile' => $validated['mobile'],
            ]);
        }

        $vendor->update($this->mapVendorPayload($request, $validated));
    }

    /**
     * @param  array<string, mixed>  $wizard
     */
    private function mergeWizardIntoRequest(Request $request, array $wizard): void
    {
        $scalars = collect($wizard)->filter(fn ($v) => is_scalar($v) || $v === null)->toArray();
        $request->merge($scalars);
    }

    /**
     * Final edit submit only sends the active tab's fields — backfill from DB.
     */
    private function mergeExistingVendorIntoRequest(Request $request, Vendor $vendor): void
    {
        $user = $vendor->user_id
            ? Users::where('user_id', $vendor->user_id)->first()
            : null;

        $existing = [
            'owner_name' => $vendor->owner_name ?: $user?->name,
            'mobile' => $vendor->mobile ?: $user?->mobile,
            'email' => $vendor->email ?: $user?->email,
            'dob' => $vendor->dob ? $vendor->dob->format('Y-m-d') : null,
            'gender' => $vendor->gender,
            'business_name' => $vendor->business_name,
            'business_email' => $vendor->business_email,
            'business_phone' => $vendor->business_phone,
            'business_description' => $vendor->business_description,
            'tax_name' => $vendor->tax_name,
            'tax_number' => $vendor->tax_number,
            'pan_number' => $vendor->pan_number,
            'gst_number' => $vendor->gst_number,
            'address' => $vendor->address,
            'latitude' => $vendor->latitude,
            'longitude' => $vendor->longitude,
            'bank_name' => $vendor->bank_name,
            'branch_name' => $vendor->branch_name,
            'bank_account' => $vendor->bank_account,
            'account_holder_name' => $vendor->account_holder_name,
            'ifsc_code' => $vendor->ifsc_code,
            'account_type' => $vendor->account_type,
            'commission_percent' => $vendor->commission_percent,
            'approval_status' => $vendor->approval_status,
        ];

        foreach ($existing as $key => $value) {
            if ($request->has($key)) {
                continue;
            }

            if ($value !== null && $value !== '') {
                $request->merge([$key => $value]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $wizard
     * @return array<string, mixed>
     */
    private function mergeWizardFileUploads(Request $request, string $tab, array $wizard): array
    {
        $fileMap = match ($tab) {
            'personal' => ['profile_image' => 'vendors'],
            'documents' => [
                'business_logo' => 'vendors',
                'business_banner' => 'vendors',
                'shop_image' => 'vendors',
                'aadhaar_card' => 'vendors/documents',
                'aadhaar_card_front' => 'vendors/documents',
                'aadhaar_card_back' => 'vendors/documents',
                'pan_card' => 'vendors/documents',
                'gst_file' => 'vendors/documents',
                'food_license_file' => 'vendors/documents',
            ],
            default => [],
        };

        foreach ($fileMap as $field => $dir) {
            $uploaded = $this->uploadFile($request, $field, $dir);
            if ($uploaded) {
                $wizard[$field] = $uploaded;
            }
        }

        return $wizard;
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

    protected function resolveVendorEmail(array $validated): string
    {
        $email = trim((string) ($validated['email'] ?? ''));
        if ($email !== '') {
            return $email;
        }

        $mobile = preg_replace('/\D/', '', (string) ($validated['mobile'] ?? ''));

        return 'vendor_' . ($mobile !== '' ? $mobile : Str::random(8)) . '@noemail.moaahar.local';
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
            'aadhaar_card_front' => 'vendors/documents',
            'aadhaar_card_back' => 'vendors/documents',
            'pan_card' => 'vendors/documents',
            'gst_file' => 'vendors/documents',
            'food_license_file' => 'vendors/documents',
        ];

        foreach ($fileMap as $field => $dir) {
            $uploaded = $this->uploadFile($request, $field, $dir);
            if ($uploaded) {
                $data[$field] = $uploaded;
            } elseif (!empty($validated[$field]) && is_string($validated[$field])) {
                $data[$field] = $validated[$field];
            }
        }

        if (!empty($data['aadhaar_card_front']) && empty($data['aadhaar_card'])) {
            $data['aadhaar_card'] = $data['aadhaar_card_front'];
        }

        return $data;
    }
}
