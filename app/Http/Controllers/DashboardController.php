<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Banner;
use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductTradingVideo;
use App\Models\QrCode;
use App\Models\RedemptionRequest;
use App\Models\Scan;
use App\Models\User;
use App\Models\VerificationLog;
use App\Models\Wallet;
use App\Services\Reward\RedemptionService;
use App\Services\Wallet\WalletService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if ($user->isConsumer()) {
            return $this->consumerDashboard($request);
        }

        $companyId = $user->company_id;

        $scope = fn (Builder $query) => $companyId !== null
            ? $query->where('company_id', $companyId)
            : $query;

        // Deliberately lean. "Verified QR codes", "verifications" and "scans"
        // are the same event written to three tables and always read alike, so
        // only the verification count is shown; the scan-result breakdown below
        // covers the detail. Reward points live in the Point Management panel
        // rather than being repeated here.
        $stats = [
            'products' => $scope(Product::query())->count(),
            'batches' => $scope(Batch::query())->count(),
            'qr_codes' => $scope(QrCode::query())->count(),
            'verifications' => $scope(VerificationLog::query())->count(),
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

        // The two panels below are gated on the same permissions as their
        // modules, so a brand or dealer account never sees company-wide user
        // counts or point balances it has no business reading.
        $userPanel = $user->can('users.view') ? $this->userManagement($companyId) : null;
        $pointPanel = $user->can('wallets.view') ? $this->pointManagement($companyId) : null;

        return view('dashboard', compact(
            'stats',
            'series',
            'resultBreakdown',
            'recentActivity',
            'topProducts',
            'userPanel',
            'pointPanel',
        ));
    }

    /**
     * User-management summary: headline counts, a role breakdown and the
     * newest sign-ups.
     *
     * @return array<string, mixed>
     */
    private function userManagement(?int $companyId): array
    {
        $scope = fn (): Builder => User::query()
            ->when($companyId !== null, fn (Builder $q) => $q->where('company_id', $companyId));

        $roleCounts = DB::table('model_has_roles as mhr')
            ->join('roles as r', 'r.id', '=', 'mhr.role_id')
            ->join('users as u', 'u.id', '=', 'mhr.model_id')
            ->where('mhr.model_type', User::class)
            ->whereNull('u.deleted_at')
            ->when($companyId !== null, fn ($q) => $q->where('u.company_id', $companyId))
            ->selectRaw('r.name as role, COUNT(*) as total')
            ->groupBy('r.name')
            ->orderByDesc('total')
            ->pluck('total', 'role');

        return [
            'total' => $scope()->count(),
            'active' => $scope()->where('status', 'active')->count(),
            'suspended' => $scope()->where('status', 'suspended')->count(),
            'new_this_month' => $scope()->where('created_at', '>=', now()->startOfMonth())->count(),
            'roles' => $roleCounts,
            'recent' => $scope()
                ->with('roles:id,name', 'city:id,name')
                ->latest()
                ->limit(5)
                ->get(['id', 'uuid', 'name', 'phone', 'status', 'city_id', 'created_at']),
        ];
    }

    /**
     * Point / wallet summary: what has been issued, what is still sitting in
     * wallets, and what is waiting on a payout decision.
     *
     * @return array<string, mixed>
     */
    private function pointManagement(?int $companyId): array
    {
        $wallets = fn (): Builder => Wallet::query()
            ->when($companyId !== null, fn (Builder $q) => $q->where('company_id', $companyId));

        $requests = fn (): Builder => RedemptionRequest::query()
            ->when($companyId !== null, fn (Builder $q) => $q->where('company_id', $companyId));

        return [
            'issued' => (float) $wallets()->sum('lifetime_credited'),
            'redeemed' => (float) $wallets()->sum('lifetime_debited'),
            // What the business still owes — the live liability.
            'outstanding' => (float) $wallets()->sum('balance'),
            'wallets' => $wallets()->count(),
            'frozen' => $wallets()->where('status', Wallet::STATUS_FROZEN)->count(),

            'pending_count' => $requests()->where('status', RedemptionRequest::STATUS_PENDING)->count(),
            'pending_amount' => (float) $requests()->where('status', RedemptionRequest::STATUS_PENDING)->sum('amount'),
            'approved_count' => $requests()->where('status', RedemptionRequest::STATUS_APPROVED)->count(),
            'approved_amount' => (float) $requests()->where('status', RedemptionRequest::STATUS_APPROVED)->sum('amount'),
            'rejected_count' => $requests()->where('status', RedemptionRequest::STATUS_REJECTED)->count(),

            'top_earners' => $wallets()
                ->where('type', Wallet::TYPE_REWARD)
                ->where('balance', '>', 0)
                ->with('user:id,uuid,name,phone')
                ->orderByDesc('balance')
                ->limit(5)
                ->get(['id', 'user_id', 'balance', 'lifetime_credited']),
        ];
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
            ->limit(5)
            ->get();

        // Consumers have no company of their own, so fall back to the first
        // company's content rather than showing an empty home screen.
        $companyId = $user->company_id ?? Company::query()->orderBy('id')->value('id');

        $banners = Banner::query()
            ->active()
            ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
            ->ordered()
            ->get();

        $products = Product::query()
            ->where('status', 'active')
            ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'sku', 'image_path', 'reward_points']);

        // Welcome video: the newest active one this user has not been shown.
        // A null watermark means they have never seen any, so the first open
        // pops up; publishing a newer video pops up again.
        $welcomeVideo = ProductTradingVideo::query()
            ->active()
            ->with('product:id,name')
            ->when(
                $user->last_seen_trading_video_id,
                fn (Builder $q, $seenId) => $q->where('id', '>', $seenId),
            )
            ->orderByDesc('id')
            ->first();

        return view('dashboard-consumer', compact('stats', 'wallet', 'recentScans', 'banners', 'products', 'welcomeVideo'));
    }
}
