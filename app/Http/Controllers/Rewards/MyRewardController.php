<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rewards;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reward\StoreRedemptionRequest;
use App\Models\RedemptionRequest;
use App\Models\VerificationLog;
use App\Services\Reward\RedemptionService;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

/**
 * The signed-in user's own rewards area: scan history, wallet balance and
 * point-redemption requests.
 */
class MyRewardController extends Controller
{
    public function __construct(
        private readonly RedemptionService $redemptions,
        private readonly WalletService $wallets,
    ) {
    }

    /**
     * The user's verification (scan) history — the products they verified and
     * the points earned.
     */
    public function scans(Request $request): View
    {
        $verifications = VerificationLog::query()
            ->with(['product:id,name,sku', 'qrCode:id,code'])
            ->where('user_id', $request->user()->id)
            ->latest('verified_at')
            ->paginate(20);

        $summary = [
            'total_scans' => VerificationLog::where('user_id', $request->user()->id)->count(),
            'points_earned' => (int) VerificationLog::where('user_id', $request->user()->id)->sum('reward_points'),
        ];

        return view('rewards.my-scans', compact('verifications', 'summary'));
    }

    /**
     * The user's wallet + redemption requests, with a request-payout form.
     */
    public function wallet(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->wallets->getOrCreateWallet($user);

        return view('rewards.my-rewards', [
            'wallet' => $wallet,
            'available' => $this->redemptions->availableBalance($user),
            'requests' => RedemptionRequest::query()
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(10),
        ]);
    }

    public function requestPayout(StoreRedemptionRequest $request): RedirectResponse
    {
        try {
            $this->redemptions->createRequest(
                user: $request->user(),
                amount: (float) $request->validated('amount'),
                method: $request->validated('method'),
                payoutDetails: $request->payoutDetails(),
                note: $request->validated('note'),
            );
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('my.rewards')->with('success', 'Redemption request submitted. It is pending approval.');
    }
}
