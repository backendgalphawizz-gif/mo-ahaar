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

            $wallet->balance = (float) $wallet->balance + $amount;
            $wallet->save();

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

            $wallet->balance = (float) $wallet->balance - $amount;
            $wallet->pending_balance = (float) $wallet->pending_balance + $amount;
            $wallet->save();

            $transaction = DriverTransaction::create([
                'driver_id' => $driverId,
                'transaction_ref' => $this->generateTransactionRef(),
                'type' => DriverTransaction::TYPE_DEBIT,
                'status' => DriverTransaction::STATUS_PENDING,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'title' => 'Withdrawal',
                'subtitle' => 'Bank transfer',
                'withdrawal_id' => $withdrawal->withdrawal_id,
            ]);

            return [
                'withdrawal' => $withdrawal,
                'transaction' => $transaction,
                'wallet' => $wallet->fresh(),
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

    public function generateTransactionRef(): string
    {
        return 'TXN-' . strtoupper(substr(uniqid(), -7)) . random_int(100, 999);
    }

    public static function formatInr(float $amount): string
    {
        return '₹' . number_format($amount, 0);
    }
}
