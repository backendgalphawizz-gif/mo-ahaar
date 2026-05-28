<?php

namespace App\Services;

use App\Models\DeliveryAssignment;
use App\Models\DriverTransaction;
use App\Models\DriverWallet;
use App\Models\DriverWithdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DriverWalletService
{
    public const MIN_WITHDRAW_AMOUNT = 100;

    public function getOrCreateWallet(int $driverId): ?DriverWallet
    {
        if (!Schema::hasTable('driver_wallets')) {
            return null;
        }

        return DriverWallet::firstOrCreate(
            ['driver_id' => $driverId],
            ['balance' => 0, 'pending_balance' => 0, 'currency' => 'INR']
        );
    }

    public function creditForDelivery(int $driverId, DeliveryAssignment $assignment): ?DriverTransaction
    {
        if (!Schema::hasTable('driver_transactions')) {
            return null;
        }

        $exists = DriverTransaction::where('driver_id', $driverId)
            ->where('assignment_id', $assignment->assignment_id)
            ->where('type', DriverTransaction::TYPE_CREDIT)
            ->exists();

        if ($exists) {
            return null;
        }

        $assignment->loadMissing(['order.customer.user']);
        $order = $assignment->order;
        $customerName = $order?->customer?->user?->name ?? 'Customer';
        $orderRef = $order?->order_number ?? ('#OID' . $assignment->order_id);

        return DB::transaction(function () use ($driverId, $assignment, $customerName, $orderRef, $order) {
            $wallet = $this->getOrCreateWallet($driverId);
            $amount = (float) $assignment->payout_amount;

            $newBalance = round((float) $wallet->balance + $amount, 2);
            
            $wallet->update([
                'balance' => $newBalance,
            ]);
            
            $wallet->refresh();

            return DriverTransaction::create([
                'driver_id' => $driverId,
                'transaction_ref' => $this->generateTransactionRef(),
                'type' => DriverTransaction::TYPE_CREDIT,
                'status' => DriverTransaction::STATUS_COMPLETED,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'title' => $customerName,
                'subtitle' => $orderRef,
                'order_id' => $assignment->order_id,
                'assignment_id' => $assignment->assignment_id,
            ]);
        });
    }

    /**
     * @return array{withdrawal: DriverWithdrawal, transaction: DriverTransaction, wallet: DriverWallet}
     */
    public function requestWithdrawal(int $driverId, float $amount): array
    {
        return DB::transaction(function () use ($driverId, $amount) {
            $wallet = $this->lockWallet($driverId);
            $wallet = $this->reconcileWallet($driverId, $wallet);
            $available = $wallet->availableBalance();

            if ($amount < self::MIN_WITHDRAW_AMOUNT) {
                throw new \InvalidArgumentException('Minimum withdrawal amount is ₹' . self::MIN_WITHDRAW_AMOUNT);
            }

            if ($amount > $available) {
                throw new \InvalidArgumentException('Insufficient wallet balance');
            }

            $withdrawal = DriverWithdrawal::create([
                'driver_id' => $driverId,
                'amount' => $amount,
                'status' => DriverWithdrawal::STATUS_PENDING,
            ]);

            // Keep ledger balance unchanged for pending withdrawals.
            // Available balance is derived as (balance - pending_balance).
            $newPendingBalance = round((float) $wallet->pending_balance + $amount, 2);
            
            $wallet->update([
                'pending_balance' => $newPendingBalance,
            ]);
            
            $wallet->refresh();

            $transaction = DriverTransaction::create([
                'driver_id' => $driverId,
                'transaction_ref' => $this->generateTransactionRef(),
                'type' => DriverTransaction::TYPE_DEBIT,
                'status' => DriverTransaction::STATUS_PENDING,
                'amount' => $amount,
                'balance_after' => $wallet->availableBalance(),
                'title' => 'Withdrawal',
                'subtitle' => 'Bank transfer',
                'withdrawal_id' => $withdrawal->withdrawal_id,
            ]);

            return [
                'withdrawal' => $withdrawal,
                'transaction' => $transaction,
                'wallet' => $wallet,
            ];
        });
    }

    public function lockWallet(int $driverId): DriverWallet
    {
        $wallet = DriverWallet::where('driver_id', $driverId)->lockForUpdate()->first();

        if (!$wallet) {
            $this->getOrCreateWallet($driverId);

            return DriverWallet::where('driver_id', $driverId)->lockForUpdate()->first();
        }

        return $wallet;
    }

    public function reconcileWallet(int $driverId, ?DriverWallet $wallet = null): DriverWallet
    {
        $wallet = $wallet ?: $this->getOrCreateWallet($driverId);

        if (!$wallet || !Schema::hasTable('driver_transactions')) {
            return $wallet;
        }

        $completedCredit = (float) DriverTransaction::where('driver_id', $driverId)
            ->where('type', DriverTransaction::TYPE_CREDIT)
            ->where('status', DriverTransaction::STATUS_COMPLETED)
            ->sum('amount');

        $completedDebit = (float) DriverTransaction::where('driver_id', $driverId)
            ->where('type', DriverTransaction::TYPE_DEBIT)
            ->where('status', DriverTransaction::STATUS_COMPLETED)
            ->sum('amount');

        $pendingDebit = (float) DriverTransaction::where('driver_id', $driverId)
            ->where('type', DriverTransaction::TYPE_DEBIT)
            ->where('status', DriverTransaction::STATUS_PENDING)
            ->sum('amount');

        $calculatedBalance = round($completedCredit - $completedDebit, 2);
        $calculatedPending = round($pendingDebit, 2);

        if (
            abs(((float) $wallet->balance - $calculatedBalance)) >= 0.01
            || abs(((float) $wallet->pending_balance - $calculatedPending)) >= 0.01
        ) {
            $wallet->update([
                'balance' => $calculatedBalance,
                'pending_balance' => $calculatedPending,
            ]);
            $wallet->refresh();
        }

        return $wallet;
    }

    public function generateTransactionRef(): string
    {
        return 'TXN-' . strtoupper(substr(uniqid(), -7)) . random_int(100, 999);
    }

    public static function formatInr(float $amount): string
    {
        return '₹' . number_format($amount, 0);
    }
}
