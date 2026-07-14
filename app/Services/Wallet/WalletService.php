<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Audit\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Authoritative money/points ledger. Every balance change goes through here so
 * the running balance, lifetime totals and audit trail stay consistent. All
 * mutations are row-locked and transactional; crediting a given source is
 * idempotent via a deterministic reference.
 */
class WalletService
{
    public function __construct(private readonly ActivityLogger $logger)
    {
    }

    public function getOrCreateWallet(User $user, string $type = Wallet::TYPE_REWARD): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['company_id' => $user->company_id, 'balance' => 0, 'status' => Wallet::STATUS_ACTIVE],
        );
    }

    /**
     * Credit a wallet. If $reference is supplied and already exists, the
     * existing transaction is returned unchanged (idempotent).
     *
     * @param array<string, mixed> $meta
     */
    public function credit(
        Wallet $wallet,
        float $amount,
        string $reason,
        ?Model $source = null,
        ?string $reference = null,
        ?string $description = null,
        array $meta = [],
    ): WalletTransaction {
        return $this->move(WalletTransaction::DIRECTION_CREDIT, $wallet, $amount, $reason, $source, $reference, $description, $meta);
    }

    /**
     * Debit a wallet; throws InsufficientBalanceException when the balance is
     * too low.
     *
     * @param array<string, mixed> $meta
     */
    public function debit(
        Wallet $wallet,
        float $amount,
        string $reason,
        ?Model $source = null,
        ?string $reference = null,
        ?string $description = null,
        array $meta = [],
    ): WalletTransaction {
        return $this->move(WalletTransaction::DIRECTION_DEBIT, $wallet, $amount, $reason, $source, $reference, $description, $meta);
    }

    /**
     * Credit a reward wallet exactly once for a given source model.
     */
    public function creditRewardForSource(
        User $user,
        float $amount,
        Model $source,
        string $reason = WalletTransaction::REASON_VERIFICATION_REWARD,
        ?string $description = null,
    ): ?WalletTransaction {
        if ($amount <= 0) {
            return null;
        }

        $wallet = $this->getOrCreateWallet($user, Wallet::TYPE_REWARD);
        $reference = $this->sourceReference($reason, $source);

        return $this->credit($wallet, $amount, $reason, $source, $reference, $description);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function move(
        string $direction,
        Wallet $wallet,
        float $amount,
        string $reason,
        ?Model $source,
        ?string $reference,
        ?string $description,
        array $meta,
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Wallet movement amount must be positive.');
        }

        $reference ??= 'wtx-'.Str::lower(Str::ulid());

        return DB::transaction(function () use ($direction, $wallet, $amount, $reason, $source, $reference, $description, $meta): WalletTransaction {
            // Idempotency: a reference is used at most once.
            $existing = WalletTransaction::where('reference', $reference)->first();
            if ($existing !== null) {
                return $existing;
            }

            /** @var Wallet $locked */
            $locked = Wallet::whereKey($wallet->getKey())->lockForUpdate()->firstOrFail();

            $current = (float) $locked->balance;

            if ($direction === WalletTransaction::DIRECTION_DEBIT && $current < $amount) {
                throw new InsufficientBalanceException(
                    "Insufficient balance: needed {$amount}, available {$current}.",
                );
            }

            $newBalance = $direction === WalletTransaction::DIRECTION_CREDIT
                ? $current + $amount
                : $current - $amount;

            $locked->balance = $newBalance;
            if ($direction === WalletTransaction::DIRECTION_CREDIT) {
                $locked->lifetime_credited = (float) $locked->lifetime_credited + $amount;
            } else {
                $locked->lifetime_debited = (float) $locked->lifetime_debited + $amount;
            }
            $locked->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $locked->id,
                'user_id' => $locked->user_id,
                'company_id' => $locked->company_id,
                'direction' => $direction,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'reason' => $reason,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
                'reference' => $reference,
                'description' => $description,
                'status' => 'completed',
                'meta' => $meta === [] ? null : $meta,
            ]);

            $this->logger->log(
                event: "wallet_{$direction}",
                subject: $transaction,
                description: ucfirst($direction)." of {$amount} ({$reason})",
                properties: ['balance_after' => $newBalance],
                logName: 'wallet',
                causerId: $locked->user_id,
            );

            return $transaction;
        });
    }

    private function sourceReference(string $reason, Model $source): string
    {
        return $reason.'-'.Str::of($source->getMorphClass())->afterLast('\\')->lower().'-'.$source->getKey();
    }
}
