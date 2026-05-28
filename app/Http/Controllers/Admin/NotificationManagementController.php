<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Customers;
use App\Models\Users;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class NotificationManagementController extends Controller
{
    public function index()
    {
        $title = 'Notification Management';
        $notifications = AdminNotification::orderByDesc('id')->paginate(20);
        $totalSent = AdminNotification::count();
        $sentThisWeek = AdminNotification::where('created_at', '>=', Carbon::now()->startOfWeek())->count();
        $sentToday = AdminNotification::whereDate('created_at', Carbon::today())->count();

        return view('admin.notifications.index', compact('title', 'notifications', 'totalSent', 'sentThisWeek', 'sentToday'));
    }

    public function recipients(Request $request): JsonResponse
    {
        $type = $request->query('type');
        $items = [];

        if ($type === 'users') {
            $items = Customers::leftJoin('users', 'users.user_id', '=', 'customers.user_id')
                ->select('customers.customer_id as id', 'users.name as name', 'users.email as email')
                ->orderBy('users.name')
                ->get()
                ->map(function ($row) {
                    $label = trim(($row->name ?? 'Customer') . (!empty($row->email) ? ' (' . $row->email . ')' : ''));

                    return ['id' => (int) $row->id, 'label' => $label];
                })
                ->values()
                ->all();
        } elseif ($type === 'drivers') {
            $items = Users::where('role_type', Users::DRIVER_APP_ROLE_TYPE)
                ->where('status', '1')
                ->select('user_id as id', 'name', 'email', 'mobile')
                ->orderBy('name')
                ->get()
                ->map(function ($row) {
                    $label = trim(($row->name ?: 'Delivery Partner') . (!empty($row->mobile) ? ' (+91 ' . $row->mobile . ')' : ''));

                    return ['id' => (int) $row->id, 'label' => $label];
                })
                ->values()
                ->all();
        }

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_type' => 'required|in:users,drivers',
            'recipient_scope' => 'required|in:all,specific',
            'recipient_id' => 'nullable|integer',
            'title' => 'nullable|string|max:190',
            'message' => 'required|string|max:5000',
        ]);

        if ($validated['recipient_scope'] === 'specific' && empty($validated['recipient_id'])) {
            return back()->withInput()->withErrors(['recipient_id' => 'Please select a recipient.']);
        }

        $recipientName = null;
        $recipientId = $validated['recipient_scope'] === 'specific' ? (int) $validated['recipient_id'] : null;

        if ($validated['recipient_scope'] === 'all') {
            $recipientName = match ($validated['target_type']) {
                'users' => 'All Users',
                'drivers' => 'All Delivery Partners',
            };
        } else {
            $recipientName = $this->resolveRecipientName($validated['target_type'], $recipientId);
            if (!$recipientName) {
                return back()->withInput()->withErrors(['recipient_id' => 'Selected recipient not found.']);
            }
        }

        AdminNotification::create([
            'target_type' => $validated['target_type'],
            'recipient_scope' => $validated['recipient_scope'],
            'recipient_id' => $recipientId,
            'recipient_name' => $recipientName,
            'title' => trim((string) ($validated['title'] ?? 'Admin Notification')),
            'message' => $validated['message'],
            'sent_by' => session('user_id'),
        ]);

        $this->dispatchPushNotification(
            $validated['target_type'],
            $validated['recipient_scope'],
            $recipientId,
            trim((string) ($validated['title'] ?? 'Admin Notification')),
            $validated['message']
        );

        return redirect()->route('admin.notifications.index')->with('success', 'Notification sent successfully.');
    }

    protected function dispatchPushNotification(
        string $targetType,
        string $recipientScope,
        ?int $recipientId,
        string $title,
        string $body
    ): void {
        $firebase = new FirebaseService();

        if ($targetType === 'users') {
            if ($recipientScope === 'all') {
                $tokens = Users::where('role_type', Users::CUSTOMER_APP_ROLE_TYPE)
                    ->whereNotNull('fcm_id')
                    ->where('fcm_id', '!=', '')
                    ->pluck('fcm_id')
                    ->all();

                $firebase->sendToTokens($tokens, $title, $body);
            } else {
                $user = Customers::leftJoin('users', 'users.user_id', '=', 'customers.user_id')
                    ->where('customers.customer_id', $recipientId)
                    ->whereNotNull('users.fcm_id')
                    ->where('users.fcm_id', '!=', '')
                    ->select('users.fcm_id')
                    ->first();

                if ($user?->fcm_id) {
                    $firebase->sendToToken($user->fcm_id, $title, $body);
                }
            }
        } elseif ($targetType === 'drivers') {
            if ($recipientScope === 'all') {
                $tokens = Users::where('role_type', Users::DRIVER_APP_ROLE_TYPE)
                    ->where('status', '1')
                    ->whereNotNull('fcm_id')
                    ->where('fcm_id', '!=', '')
                    ->pluck('fcm_id')
                    ->all();
                $firebase->sendToTokens($tokens, $title, $body);
            } else {
                $driver = Users::where('user_id', $recipientId)
                    ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
                    ->whereNotNull('fcm_id')
                    ->where('fcm_id', '!=', '')
                    ->first(['fcm_id']);
                if ($driver?->fcm_id) {
                    $firebase->sendToToken($driver->fcm_id, $title, $body);
                }
            }
        }
    }

    protected function resolveRecipientName(string $type, int $id): ?string
    {
        if ($type === 'users') {
            $row = Customers::leftJoin('users', 'users.user_id', '=', 'customers.user_id')
                ->where('customers.customer_id', $id)
                ->select('users.name', 'users.email')
                ->first();

            if (!$row) {
                return null;
            }

            return trim(($row->name ?: 'Customer') . (!empty($row->email) ? ' (' . $row->email . ')' : ''));
        }

        if ($type === 'drivers') {
            $row = Users::where('user_id', $id)
                ->where('role_type', Users::DRIVER_APP_ROLE_TYPE)
                ->first(['name', 'mobile']);
            if (!$row) {
                return null;
            }

            return trim(($row->name ?: 'Delivery Partner') . (!empty($row->mobile) ? ' (+91 ' . $row->mobile . ')' : ''));
        }

        return null;
    }
}
