<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Product;
use App\Models\QrCode;
use App\Models\Scan;
use App\Models\VerificationLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $request->user()->company_id;

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
}
