<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\RedemptionRequest;
use App\Models\Scan;
use App\Models\VerificationLog;
use App\Services\Reward\RedemptionService;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly WalletService $wallets,
        private readonly RedemptionService $redemptions,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        // Self-registered consumers (karigar / contractor …) have no management
        // permissions — they scan to earn points. Show them their own scan +
        // wallet + rewards home instead of the company-wide admin dashboard.
        if (! $user->canAny(['products.view', 'batches.view', 'qr-codes.view', 'users.view', 'wallets.credit'])) {
            return $this->consumerDashboard($request);
        }

        $companyId = $user->company_id;

        $scope = fn (Builder $query) => $companyId !== null
            ? $query->where('company_id', $companyId)
            : $query;

        $stats = [
            'products' => $scope(Product::query())->count(),
            'batches' => $scope(Batch::query())->count(),
            'qr_codes' => $scope(QrCode::query())->count(),
            'verified' => $scope(QrCode::query())->where('status', QrCode::STATUS_VERIFIED)->count(),
            'verifications' => $scope(VerificationLog::query())->count(),
            'scans' => $scope(Scan::query())->count(),
            'fraud_scans' => $scope(Scan::query())->where('is_fraud_suspected', true)->count(),
            'reward_points' => (int) $scope(VerificationLog::query())->sum('reward_points'),
        ];

        // Verifications per day for the last 14 days (chart series).
        $series = collect(range(13, 0))->map(function (int $daysAgo) use ($scope): array {
            $day = now()->subDays($daysAgo)->startOfDay();

            return [
                'label' => $day->format('d M'),
                'count' => $scope(VerificationLog::query())
                    ->whereBetween('verified_at', [$day, (clone $day)->endOfDay()])
                    ->count(),
            ];
        })->values();

        // Scan result breakdown.
        $resultBreakdown = $scope(Scan::query())
            ->selectRaw('result, COUNT(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        $recentActivity = ActivityLog::query()
            ->with('causer')
            ->latest()
            ->limit(8)
            ->get();

        $topProducts = $scope(VerificationLog::query())
            ->selectRaw('product_id, COUNT(*) as verifications')
            ->groupBy('product_id')
            ->orderByDesc('verifications')
            ->with('product:id,name,sku')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'stats',
            'series',
            'resultBreakdown',
            'recentActivity',
            'topProducts',
        ));
    }

    /**
     * The scan-to-earn home for consumer accounts: a prominent QR scan action,
     * their wallet balance and reward-point totals.
     */
    private function consumerDashboard(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->wallets->getOrCreateWallet($user);

        $stats = [
            'balance' => (float) $wallet->balance,
            'redeemable' => $this->redemptions->availableBalance($user),
            'lifetime_earned' => (float) $wallet->lifetime_credited,
            'redeemed' => (float) $wallet->lifetime_debited,
            'total_scans' => VerificationLog::where('user_id', $user->id)->count(),
            'points_earned' => (int) VerificationLog::where('user_id', $user->id)->sum('reward_points'),
            'redemptions_count' => RedemptionRequest::where('user_id', $user->id)->count(),
            'redemptions_pending' => RedemptionRequest::where('user_id', $user->id)
                ->where('status', RedemptionRequest::STATUS_PENDING)
                ->count(),
        ];

        $recentScans = VerificationLog::query()
            ->with(['product:id,name,sku'])
            ->where('user_id', $user->id)
            ->latest('verified_at')
            ->limit(6)
            ->get();

        return view('dashboard-consumer', compact('stats', 'wallet', 'recentScans'));
    }
}
