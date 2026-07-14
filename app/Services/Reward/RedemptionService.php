<?php

declare(strict_types=1);

namespace App\Services\Reward;

use App\Models\RedemptionRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Audit\ActivityLogger;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Points redemption workflow: a consumer requests a payout, an admin approves
 * (optionally attaching proof) which debits the wallet, or rejects it.
 */
class RedemptionService
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly ActivityLogger $logger,
    ) {
    }

    /**
     * Balance a user may still request: wallet balance minus points already tied
     * up in pending requests.
     */
    public function availableBalance(User $user): float
    {
        $wallet = $this->wallets->getOrCreateWallet($user, Wallet::TYPE_REWARD);

        $pending = (float) RedemptionRequest::query()
            ->where('user_id', $user->id)
            ->where('status', RedemptionRequest::STATUS_PENDING)
            ->sum('amount');

        return max(0.0, (float) $wallet->balance - $pending);
    }

    /**
     * @param array<string, mixed> $payoutDetails
     */
    public function createRequest(User $user, float $amount, string $method, array $payoutDetails, ?string $note = null): RedemptionRequest
    {
        if ($amount <= 0) {
            throw new RuntimeException('Redemption amount must be greater than zero.');
        }

        $available = $this->availableBalance($user);

        if ($amount > $available) {
            throw new RuntimeException("Requested amount exceeds your available balance ({$available}).");
        }

        $wallet = $this->wallets->getOrCreateWallet($user, Wallet::TYPE_REWARD);

        $request = RedemptionRequest::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'company_id' => $user->company_id,
            'reference' => $this->reference(),
            'amount' => $amount,
            'method' => $method,
            'payout_details' => $payoutDetails === [] ? null : $payoutDetails,
            'note' => $note,
            'status' => RedemptionRequest::STATUS_PENDING,
        ]);

        $this->logger->log('redemption_requested', $request, "Redemption {$request->reference} requested for {$amount}", logName: 'redemption', causerId: $user->id);

        return $request;
    }

    /**
     * Approve a request: debit the wallet and record the reviewer + proof.
     */
    public function approve(RedemptionRequest $request, User $admin, ?string $attachmentPath = null, ?string $reviewNote = null): RedemptionRequest
    {
        return DB::transaction(function () use ($request, $admin, $attachmentPath, $reviewNote): RedemptionRequest {
            /** @var RedemptionRequest $req */
            $req = RedemptionRequest::whereKey($request->getKey())->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new RuntimeException('This request has already been reviewed.');
            }

            $wallet = $req->wallet ?? $this->wallets->getOrCreateWallet($req->user, Wallet::TYPE_REWARD);

            // Debit the points from the user's wallet.
            $transaction = $this->wallets->debit(
                wallet: $wallet,
                amount: (float) $req->amount,
                reason: WalletTransaction::REASON_REDEMPTION,
                source: $req,
                reference: 'redemption-'.$req->id,
                description: "Redemption {$req->reference} approved",
            );

            $req->forceFill([
                'status' => RedemptionRequest::STATUS_APPROVED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_note' => $reviewNote,
                'attachment_path' => $attachmentPath,
                'wallet_transaction_id' => $transaction->id,
            ])->save();

            $this->logger->log('redemption_approved', $req, "Redemption {$req->reference} approved (−{$req->amount})", logName: 'redemption', causerId: $admin->id);

            return $req;
        });
    }

    public function reject(RedemptionRequest $request, User $admin, string $reason): RedemptionRequest
    {
        return DB::transaction(function () use ($request, $admin, $reason): RedemptionRequest {
            /** @var RedemptionRequest $req */
            $req = RedemptionRequest::whereKey($request->getKey())->lockForUpdate()->firstOrFail();

            if (! $req->isPending()) {
                throw new RuntimeException('This request has already been reviewed.');
            }

            $req->forceFill([
                'status' => RedemptionRequest::STATUS_REJECTED,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ])->save();

            $this->logger->log('redemption_rejected', $req, "Redemption {$req->reference} rejected", logName: 'redemption', causerId: $admin->id);

            return $req;
        });
    }

    private function reference(): string
    {
        do {
            $ref = 'RDM-'.strtoupper(Str::random(8));
        } while (RedemptionRequest::where('reference', $ref)->exists());

        return $ref;
    }
}
