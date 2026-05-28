<?php

namespace App\Http\Controllers\API\DriverApp;

use App\Models\DriverTransaction;
use App\Services\DriverWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends DriverAppController
{
    public function __construct(
        private readonly DriverWalletService $walletService
    ) {}

    public function wallet(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $wallet = $this->walletService->getOrCreateWallet((int) $driver->user_id);
        $wallet = $this->walletService->reconcileWallet((int) $driver->user_id, $wallet);
        $totalBalance = $wallet ? (float) $wallet->balance : 0.0;
        $available = $wallet ? $wallet->availableBalance() : 0.0;
        $pending = $wallet ? (float) $wallet->pending_balance : 0.0;

        return response()->json([
            'status' => true,
            'message' => 'Wallet retrieved successfully',
            'data' => [
                'total_earnings' => $totalBalance,
                'available_balance' => $available,
                'pending_withdrawals' => $pending,
                'currency' => $wallet?->currency ?? 'INR',
                'total_earnings_formatted' => DriverWalletService::formatInr($totalBalance),
                'can_withdraw' => $available >= DriverWalletService::MIN_WITHDRAW_AMOUNT,
                'min_withdraw_amount' => DriverWalletService::MIN_WITHDRAW_AMOUNT,
            ],
        ], 200);
    }

    public function transactions(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        if (!Schema::hasTable('driver_transactions')) {
            return response()->json([
                'status' => true,
                'message' => 'Transactions retrieved successfully',
                'data' => [
                    'transactions' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 15,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                ],
            ], 200);
        }

        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', Rule::in(['credit', 'debit', 'pending', 'all'])],
            'status' => ['nullable', 'string', Rule::in(['completed', 'pending', 'failed', 'all'])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        $perPage = max(1, min((int) ($validated['per_page'] ?? 15), 50));
        $driverId = (int) $driver->user_id;

        $query = DriverTransaction::query()
            ->where('driver_id', $driverId)
            ->orderByDesc('transaction_id');

        if (!empty($validated['search'])) {
            $term = '%' . trim($validated['search']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('transaction_ref', 'like', $term)
                    ->orWhere('title', 'like', $term)
                    ->orWhere('subtitle', 'like', $term);
            });
        }

        $typeFilter = $validated['type'] ?? 'all';
        if ($typeFilter !== 'all') {
            $query->where('type', $typeFilter);
        }

        $statusFilter = $validated['status'] ?? 'all';
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        $paginated = $query->paginate($perPage);

        $items = collect($paginated->items())
            ->map(fn (DriverTransaction $txn) => $this->formatTransaction($txn))
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
            'data' => [
                'transactions' => $items,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
            ],
        ], 200);
    }

    public function showTransaction(Request $request, int $transactionId)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $transaction = DriverTransaction::where('transaction_id', $transactionId)
            ->where('driver_id', $driver->user_id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Transaction retrieved successfully',
            'data' => [
                'transaction' => $this->formatTransaction($transaction),
            ],
        ], 200);
    }

    public function withdraw(Request $request)
    {
        $driver = $this->resolveDriver($request);
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $amount = (float) $validated['amount'];

        try {
            $result = $this->walletService->requestWithdrawal((int) $driver->user_id, $amount);
        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'amount' => [$e->getMessage()],
            ]);
        }

        // Re-read wallet after commit to avoid stale object values.
        $wallet = $this->walletService->getOrCreateWallet((int) $driver->user_id);
        $wallet = $this->walletService->reconcileWallet((int) $driver->user_id, $wallet);
        $totalBalance = $wallet ? (float) $wallet->balance : 0.0;
        $available = $wallet ? $wallet->availableBalance() : 0.0;
        $txn = $result['transaction'];

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted successfully',
            'data' => [
                'withdrawal_id' => $result['withdrawal']->withdrawal_id,
                'transaction_ref' => '#' . $txn->transaction_ref,
                'amount' => $amount,
                'amount_formatted' => DriverWalletService::formatInr($amount),
                'status' => DriverTransaction::STATUS_PENDING,
                'wallet' => [
                    // Keep this field aligned with wallet summary UI expectation.
                    'total_earnings' => $available,
                    'total_earnings_formatted' => DriverWalletService::formatInr($available),
                    'available_balance' => $available,
                    'available_balance_formatted' => DriverWalletService::formatInr($available),
                    'ledger_balance' => $totalBalance,
                    'ledger_balance_formatted' => DriverWalletService::formatInr($totalBalance),
                ],
            ],
        ], 201);
    }

    private function formatTransaction(DriverTransaction $txn): array
    {
        $ref = '#' . $txn->transaction_ref;
        $badge = $this->transactionStatusBadge($txn);

        return [
            'transaction_id' => $txn->transaction_id,
            'transaction_ref' => $ref,
            'display_id' => $ref,
            'title' => $txn->title ?? 'Transaction',
            'subtitle' => $txn->subtitle,
            'amount' => (float) $txn->amount,
            'amount_formatted' => DriverWalletService::formatInr((float) $txn->amount),
            'type' => $txn->type,
            'type_label' => strtoupper($txn->type),
            'status' => $txn->status,
            'status_label' => $badge['text'],
            'status_badge' => $badge,
            'icon' => [
                'name' => $txn->type === DriverTransaction::TYPE_DEBIT ? 'withdraw' : 'shopping_bag',
            ],
            'order_id' => $txn->order_id,
            'assignment_id' => $txn->assignment_id,
            'withdrawal_id' => $txn->withdrawal_id,
            'balance_after' => $txn->balance_after !== null ? (float) $txn->balance_after : null,
            'created_at' => $txn->created_at?->toIso8601String(),
            'created_at_formatted' => $txn->created_at?->format('M d, Y, g:i A'),
        ];
    }

    /**
     * @return array{text: string, color: string}
     */
    private function transactionStatusBadge(DriverTransaction $txn): array
    {
        if ($txn->status === DriverTransaction::STATUS_PENDING) {
            return ['text' => 'PENDING', 'color' => 'orange'];
        }

        if ($txn->type === DriverTransaction::TYPE_CREDIT && $txn->status === DriverTransaction::STATUS_COMPLETED) {
            return ['text' => 'CREDIT', 'color' => 'green'];
        }

        if ($txn->type === DriverTransaction::TYPE_DEBIT && $txn->status === DriverTransaction::STATUS_COMPLETED) {
            return ['text' => 'DEBIT', 'color' => 'red'];
        }

        if ($txn->type === DriverTransaction::TYPE_DEBIT) {
            return ['text' => 'DEBIT', 'color' => 'red'];
        }

        return ['text' => strtoupper($txn->status), 'color' => 'gray'];
    }
}
