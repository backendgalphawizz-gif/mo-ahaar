<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DriverRequest;
use App\Models\DeliveryAssignment;
use App\Models\DriverProfile;
use App\Models\DriverTransaction;
use App\Models\DriverWallet;
use App\Models\Users;
use App\Services\DriverWalletService;
use App\Support\DriverProfileValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DeliveryManagementController extends Controller
{
    public function __construct(
        private readonly DriverWalletService $walletService
    ) {}

    private function driversQuery(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = trim((string) $request->query('status', 'all'));

        $query = Users::query()
            ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
            ->where('status', '!=', 2)
            ->orderByDesc('user_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('mobile', 'like', '%' . $search . '%')
                    ->orWhereHas('driverProfile', function ($profileQuery) use ($search) {
                        $profileQuery->where('driver_code', 'like', '%' . $search . '%')
                            ->orWhere('city', 'like', '%' . $search . '%')
                            ->orWhere('address', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($status === 'approved') {
            $query->where('approval_status', 'approved');
        } elseif ($status === 'pending') {
            $query->where('approval_status', 'pending');
        } elseif ($status === 'rejected') {
            $query->where('approval_status', 'rejected');
        }

        return $query;
    }

    public function index(Request $request)
    {
        $drivers = $this->driversQuery($request)->get();
        $driverIds = $drivers->pluck('user_id');

        $profiles = DriverProfile::whereIn('driver_id', $driverIds)->get()->keyBy('driver_id');
        $wallets = Schema::hasTable('driver_wallets')
            ? DriverWallet::whereIn('driver_id', $driverIds)->get()->keyBy('driver_id')
            : collect();

        return view('admin.delivery.index', [
            'title' => 'Delivery Partner Management',
            'drivers' => $drivers,
            'profiles' => $profiles,
            'wallets' => $wallets,
            'search' => $request->query('search', ''),
            'status' => $request->query('status', 'all'),
        ]);
    }

    public function walletTransactions(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $transactions = collect();

        if (Schema::hasTable('driver_transactions')) {
            $query = DriverTransaction::with('driver')
                ->orderByDesc('id');

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('reference', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%')
                        ->orWhere('type', 'like', '%' . $search . '%');
                });
            }

            $transactions = $query->paginate(20)->withQueryString();
        }

        return view('admin.delivery.walletTransactions', [
            'title' => 'Wallet Transactions',
            'transactions' => $transactions,
            'search' => $search,
        ]);
    }

    public function addDriver()
    {
        return view('admin.delivery.add', [
            'title' => 'Add New Driver',
            'driver' => null,
            'profile' => null,
        ]);
    }

    public function storeDriver(DriverRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $approvalStatus = $validated['approval_status'] ?? 'pending';
            $isApproved = $approvalStatus === 'approved';

            $driver = Users::create([
                'name' => trim($validated['name']),
                'email' => trim($validated['email']),
                'mobile' => $validated['mobile'],
                'password' => Hash::make($validated['password']),
                'role_type' => Users::DRIVER_APP_ROLE_TYPE,
                'status' => $isApproved ? '1' : '0',
                'approval_status' => $approvalStatus,
            ]);

            if ($request->hasFile('profile_image')) {
                $driver->profile_image = $this->storeDriverFile($request->file('profile_image'), 'profile_' . $driver->user_id);
                $driver->save();
            }

            $profile = $this->syncDriverProfile($driver->user_id, $validated, $request, null);
            $profile->driver_code = $this->generateDriverCode();
            $profile->save();

            $this->walletService->getOrCreateWallet((int) $driver->user_id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create driver: ' . $e->getMessage());
        }

        return redirect()->route('admin.delivery.index')->with('success', 'Driver created successfully.');
    }

    public function viewDriver($id, Request $request)
    {
        $driver = $this->findDriverOrFail($id);
        $profile = DriverProfile::where('driver_id', $driver->user_id)->first();
        $wallet = Schema::hasTable('driver_wallets')
            ? DriverWallet::where('driver_id', $driver->user_id)->first()
            : null;

        $search = trim((string) $request->query('search', ''));
        $deliveriesQuery = DeliveryAssignment::with(['order.customer.user', 'order.vendor', 'order.orderItems'])
            ->where('driver_id', $driver->user_id)
            ->orderByDesc('assignment_id');

        if ($search !== '') {
            $deliveriesQuery->where(function ($q) use ($search) {
                $q->where('store_name', 'like', '%' . $search . '%')
                    ->orWhereHas('order', function ($orderQuery) use ($search) {
                        $orderQuery->where('order_number', 'like', '%' . $search . '%')
                            ->orWhereHas('orderItems', function ($itemQuery) use ($search) {
                                $itemQuery->where('product_name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        $deliveries = $deliveriesQuery->get();

        return view('admin.delivery.view', compact('driver', 'profile', 'wallet', 'deliveries', 'search'));
    }

    public function editDriver($id)
    {
        $driver = $this->findDriverOrFail($id);
        $profile = DriverProfile::where('driver_id', $driver->user_id)->first();

        return view('admin.delivery.edit', [
            'title' => 'Edit Delivery Partner',
            'driver' => $driver,
            'profile' => $profile,
        ]);
    }

    public function updateDriver(DriverRequest $request, $id)
    {
        $driver = $this->findDriverOrFail($id);
        $validated = $request->validated();
        $profile = DriverProfile::firstOrCreate(['driver_id' => $driver->user_id]);

        DB::beginTransaction();
        try {
            $driver->name = trim($validated['name']);
            $driver->email = trim($validated['email']);
            $driver->mobile = $validated['mobile'];

            if (!empty($validated['password'])) {
                $driver->password = Hash::make($validated['password']);
            }

            if (isset($validated['approval_status'])) {
                $driver->approval_status = $validated['approval_status'];
                if ($validated['approval_status'] === 'approved') {
                    $driver->status = '1';
                } elseif ($validated['approval_status'] === 'rejected') {
                    $driver->status = '0';
                }
            }

            if (isset($validated['status'])) {
                $driver->status = (string) $validated['status'];
            }

            if ($request->hasFile('profile_image')) {
                $this->deleteDriverFile($driver->profile_image);
                $driver->profile_image = $this->storeDriverFile($request->file('profile_image'), 'profile_' . $driver->user_id);
            }

            $driver->save();
            $this->syncDriverProfile($driver->user_id, $validated, $request, $profile);

            if (empty($profile->driver_code)) {
                $profile->driver_code = $this->generateDriverCode();
                $profile->save();
            }

            $this->walletService->getOrCreateWallet((int) $driver->user_id);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update driver: ' . $e->getMessage());
        }

        return redirect()->route('admin.delivery.view', $driver->user_id)->with('success', 'Driver updated successfully.');
    }

    public function deleteDriver($id)
    {
        $driver = $this->findDriverOrFail($id);
        $driver->status = '2';
        $driver->save();

        return redirect()->route('admin.delivery.index')->with('success', 'Driver deleted successfully.');
    }

    public function updateApprovalStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        $driver = $this->findDriverOrFail($id);
        $driver->approval_status = $validated['approval_status'];
        $driver->status = $validated['approval_status'] === 'approved' ? '1' : '0';
        $driver->save();

        return back()->with('success', 'Driver status updated to ' . ucfirst($validated['approval_status']) . '.');
    }

    public function toggleStatus($id)
    {
        $driver = $this->findDriverOrFail($id);

        if (strtolower((string) $driver->approval_status) !== 'approved') {
            return back()->with('error', 'Only approved drivers can be activated or deactivated.');
        }

        $driver->status = (int) $driver->status === 1 ? '0' : '1';
        $driver->save();

        return back()->with('success', 'Driver active status updated.');
    }

    public function exportDriversExcel(Request $request)
    {
        $drivers = $this->driversQuery($request)->get();
        $profiles = DriverProfile::whereIn('driver_id', $drivers->pluck('user_id'))->get()->keyBy('driver_id');
        $wallets = Schema::hasTable('driver_wallets')
            ? DriverWallet::whereIn('driver_id', $drivers->pluck('user_id'))->get()->keyBy('driver_id')
            : collect();

        $fileName = 'delivery-partners-' . date('Y-m-d-H-i-s') . '.xls';
        $headers = [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($drivers, $profiles, $wallets) {
            echo "Sl No.\tDriver Code\tName\tEmail\tMobile\tCity\tWallet\tStatus\tActive\n";
            foreach ($drivers as $index => $driver) {
                $profile = $profiles[$driver->user_id] ?? null;
                $wallet = $wallets[$driver->user_id] ?? null;
                echo ($index + 1) . "\t";
                echo ($profile->driver_code ?? '') . "\t";
                echo ($driver->name ?? '') . "\t";
                echo ($driver->email ?? '') . "\t";
                echo ($driver->mobile ?? '') . "\t";
                echo ($profile->city ?? '') . "\t";
                echo number_format((float) ($wallet->balance ?? 0), 2, '.', '') . "\t";
                echo ucfirst((string) ($driver->approval_status ?? 'pending')) . "\t";
                echo ((int) $driver->status === 1 ? 'Yes' : 'No') . "\n";
            }
        };

        return response()->stream($callback, 200, $headers);
    }

    private function findDriverOrFail($id): Users
    {
        return Users::where('user_id', $id)
            ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
            ->where('status', '!=', 2)
            ->firstOrFail();
    }

    private function generateDriverCode(): string
    {
        $lastProfile = DriverProfile::whereNotNull('driver_code')->orderByDesc('profile_id')->first();
        $next = 1;
        if ($lastProfile && preg_match('/DP-(\d+)/', (string) $lastProfile->driver_code, $matches)) {
            $next = ((int) $matches[1]) + 1;
        } else {
            $next = DriverProfile::count() + 1;
        }

        return 'DP-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    private function syncDriverProfile(int $driverId, array $validated, Request $request, ?DriverProfile $profile): DriverProfile
    {
        $profile = $profile ?? DriverProfile::firstOrCreate(['driver_id' => $driverId]);

        return DriverProfileValidator::syncProfileFromRequest(
            $profile,
            $validated,
            $request,
            fn ($file, $prefix) => $this->storeDriverFile($file, $prefix),
            fn ($fileName) => $this->deleteDriverFile($fileName)
        );
    }

    private function storeDriverFile($file, string $prefix): string
    {
        $uploadPath = public_path('uploads/drivers');
        File::ensureDirectoryExists($uploadPath);

        $fileName = $prefix . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move($uploadPath, $fileName);

        return $fileName;
    }

    private function deleteDriverFile(?string $fileName): void
    {
        if (!$fileName) {
            return;
        }

        $path = public_path('uploads/drivers/' . $fileName);
        if (File::exists($path)) {
            File::delete($path);
        }
    }
}
