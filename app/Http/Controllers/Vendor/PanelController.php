<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Users;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class PanelController extends Controller
{
    public function orders()
    {
        $vendor = $this->vendorOrFail();
        $orders = Orders::with(['customer.user', 'orderItems', 'deliveryAssignment.driver'])
            ->where('vendor_id', $vendor->vendor_id)
            ->latest('order_id')
            ->paginate(10);

        $statsQuery = Orders::where('vendor_id', $vendor->vendor_id);
        $stats = [
            'new_orders' => (clone $statsQuery)->whereIn('order_status', ['pending', 'payment_pending'])->count(),
            'total_orders' => (clone $statsQuery)->count(),
            'preparing' => (clone $statsQuery)->whereIn('order_status', ['accepted', 'confirmed', 'processing'])->count(),
            'picked_up' => (clone $statsQuery)->where('order_status', 'picked_up')->count(),
            'delivered' => (clone $statsQuery)->whereIn('order_status', ['delivered', 'completed', 'success'])->count(),
            'cancelled' => (clone $statsQuery)->whereIn('order_status', ['cancelled', 'rejected'])->count(),
        ];

        return view('vendor.orders.index', compact('orders', 'stats'));
    }

    public function orderDetails($id)
    {
        $vendor = $this->vendorOrFail();
        $order = Orders::with(['customer.user', 'orderItems', 'deliveryAssignment.driver'])
            ->where('vendor_id', $vendor->vendor_id)
            ->findOrFail($id);

        return view('vendor.orders.show', compact('order'));
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $vendor = $this->vendorOrFail();
        $validated = $request->validate([
            'order_status' => ['required', Rule::in(Orders::persistableOrderStatuses())],
        ]);

        $order = Orders::where('vendor_id', $vendor->vendor_id)->findOrFail($id);
        $order->order_status = $validated['order_status'];
        $order->save();

        return back()->with('success', 'Order status updated.');
    }

    public function profile(Request $request)
    {
        $vendor = $this->vendorOrFail();
        $user = Users::find($vendor->user_id);
        $tab = in_array($request->query('tab'), ['personal', 'business', 'bank', 'documents'], true)
            ? $request->query('tab')
            : 'personal';
        $edit = $request->boolean('edit', false);

        return view('vendor.profile.show', compact('vendor', 'user', 'tab', 'edit'));
    }

    public function updateProfile(Request $request)
    {
        $vendor = $this->vendorOrFail();
        $user = Users::findOrFail($vendor->user_id);

        $validated = $request->validate([
            'owner_name' => ['nullable', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'mobile' => ['nullable', 'string', 'max:15'],
            'business_name' => ['nullable', 'string', 'max:150'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'business_description' => ['nullable', 'string', 'max:2000'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'gst_number' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:150'],
            'branch_name' => ['nullable', 'string', 'max:150'],
            'account_type' => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:30'],
            'ifsc_code' => ['nullable', 'string', 'max:20'],
            'profile_image' => ['nullable', 'image', 'max:4096'],
            'aadhaar_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'pan_card' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $vendor->fill(collect($validated)->only([
            'owner_name', 'mobile', 'business_name', 'business_email', 'business_phone',
            'address', 'business_description', 'pan_number', 'gst_number',
            'bank_name', 'branch_name', 'account_type', 'bank_account', 'ifsc_code',
        ])->toArray());

        if ($request->hasFile('profile_image')) {
            $vendor->profile_image = $this->uploadVendorFile($request->file('profile_image'), 'vendors');
        }
        if ($request->hasFile('aadhaar_card')) {
            $vendor->aadhaar_card = $this->uploadVendorFile($request->file('aadhaar_card'), 'vendors/documents');
        }
        if ($request->hasFile('pan_card')) {
            $vendor->pan_card = $this->uploadVendorFile($request->file('pan_card'), 'vendors/documents');
        }
        $vendor->save();

        $user->name = $validated['owner_name'] ?? $user->name;
        $user->email = $validated['email'] ?? $user->email;
        $user->mobile = $validated['mobile'] ?? $user->mobile;
        $user->save();

        session(['name' => $user->name]);

        return redirect()->route('vendor.profile', ['tab' => $request->input('tab', 'personal')])->with('success', 'Profile updated successfully.');
    }

    public function payments()
    {
        $vendor = $this->vendorOrFail();
        $transactions = Orders::where('vendor_id', $vendor->vendor_id)
            ->latest('order_id')
            ->limit(12)
            ->get()
            ->map(function (Orders $order) {
                return [
                    'id' => 'TXN-' . str_pad((string) $order->order_id, 3, '0', STR_PAD_LEFT),
                    'title' => 'Order ' . ($order->order_number ?? ('#' . $order->order_id)) . ' Payment',
                    'date' => optional($order->created_at)->format('Y-m-d H:i'),
                    'amount' => (float) $order->total_amount,
                    'type' => 'credit',
                    'status' => strtolower((string) $order->payment_status) === 'paid' ? 'completed' : 'pending',
                ];
            });

        return view('vendor.payments.index', compact('vendor', 'transactions'));
    }

    public function requestWithdraw(Request $request)
    {
        $vendor = $this->vendorOrFail();
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $validated['amount'];
        if ($amount > (float) $vendor->wallet_balance) {
            return back()->with('error', 'Requested amount exceeds available balance.');
        }

        Vendor::where('vendor_id', $vendor->vendor_id)->update([
            'withdrawal_amount' => DB::raw('COALESCE(withdrawal_amount,0)+' . $amount),
            'wallet_balance' => DB::raw('GREATEST(COALESCE(wallet_balance,0)-' . $amount . ',0)'),
        ]);

        return back()->with('success', 'Withdraw request sent successfully.');
    }

    public function addons()
    {
        $addons = $this->readAddons();
        return view('vendor.addons.index', compact('addons'));
    }

    public function addonCreate()
    {
        return view('vendor.addons.form', ['addon' => null]);
    }

    public function addonStore(Request $request)
    {
        $validated = $this->validateAddon($request);
        $addons = $this->readAddons();
        $nextId = $addons->max('id') ? ((int) $addons->max('id') + 1) : 1;
        $addons->push([
            'id' => $nextId,
            'name' => $validated['name'],
            'price' => (float) $validated['price'],
            'type' => $validated['type'],
            'is_active' => true,
        ]);
        $this->writeAddons($addons);

        return redirect()->route('vendor.addons.index')->with('success', 'Add on created successfully.');
    }

    public function addonEdit($id)
    {
        $addon = $this->readAddons()->firstWhere('id', (int) $id);
        abort_if(!$addon, 404);

        return view('vendor.addons.form', ['addon' => (object) $addon]);
    }

    public function addonUpdate(Request $request, $id)
    {
        $validated = $this->validateAddon($request);
        $addons = $this->readAddons();
        $index = $addons->search(fn ($addon) => (int) $addon['id'] === (int) $id);
        abort_if($index === false, 404);

        $addons[$index]['name'] = $validated['name'];
        $addons[$index]['price'] = (float) $validated['price'];
        $addons[$index]['type'] = $validated['type'];
        $this->writeAddons($addons);

        return redirect()->route('vendor.addons.index')->with('success', 'Add on updated successfully.');
    }

    public function addonToggle($id)
    {
        $addons = $this->readAddons();
        $index = $addons->search(fn ($addon) => (int) $addon['id'] === (int) $id);
        abort_if($index === false, 404);
        $addons[$index]['is_active'] = !(bool) $addons[$index]['is_active'];
        $this->writeAddons($addons);

        return back()->with('success', 'Add on status updated.');
    }

    public function addonDelete($id)
    {
        $addons = $this->readAddons()->reject(fn ($addon) => (int) $addon['id'] === (int) $id)->values();
        $this->writeAddons($addons);
        return back()->with('success', 'Add on deleted.');
    }

    private function vendorOrFail(): Vendor
    {
        $vendorId = (int) session('vendor_id');
        abort_if($vendorId <= 0, 403);

        return Vendor::findOrFail($vendorId);
    }

    private function uploadVendorFile($file, string $dir): string
    {
        $filename = uniqid('vendor_', true) . '.' . $file->getClientOriginalExtension();
        $fullPath = public_path('uploads/' . $dir);
        if (!File::exists($fullPath)) {
            File::makeDirectory($fullPath, 0755, true);
        }
        $file->move($fullPath, $filename);
        return $filename;
    }

    private function addonStoragePath(): string
    {
        $vendor = $this->vendorOrFail();
        return storage_path('app/vendor_addons/vendor_' . $vendor->vendor_id . '.json');
    }

    private function readAddons(): Collection
    {
        $path = $this->addonStoragePath();
        if (!File::exists($path)) {
            return collect([
                ['id' => 1, 'name' => 'Extra Cheese', 'price' => 250, 'type' => 'veg', 'is_active' => true],
                ['id' => 2, 'name' => 'Bacon Strips', 'price' => 300, 'type' => 'non-veg', 'is_active' => true],
                ['id' => 3, 'name' => 'Guacamole', 'price' => 200, 'type' => 'veg', 'is_active' => false],
            ]);
        }

        $decoded = json_decode((string) File::get($path), true);
        return collect(is_array($decoded) ? $decoded : []);
    }

    private function writeAddons(Collection $addons): void
    {
        $path = $this->addonStoragePath();
        $dir = dirname($path);
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        File::put($path, json_encode($addons->values()->all(), JSON_PRETTY_PRINT));
    }

    private function validateAddon(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'type' => ['required', Rule::in(['veg', 'non-veg'])],
        ]);
    }
}

