<?php

declare(strict_types=1);

namespace App\Http\Controllers\Wallet;

use App\Http\Controllers\Controller;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Wallet::class);

        $companyId = $request->user()->company_id;
        $search = trim((string) $request->string('search'));
        $type = $request->string('type')->toString();

        $wallets = Wallet::query()
            ->with('user:id,name,email,phone')
            ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
            ->when($type !== '', fn (Builder $q) => $q->where('type', $type))
            ->when($search !== '', fn (Builder $q) => $q->whereHas('user', function (Builder $u) use ($search): void {
                $u->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderByDesc('balance')
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'wallets' => (clone $wallets)->total(),
            'balance' => Wallet::query()
                ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
                ->sum('balance'),
            'credited' => Wallet::query()
                ->when($companyId, fn (Builder $q, $id) => $q->where('company_id', $id))
                ->sum('lifetime_credited'),
        ];

        return view('wallets.index', [
            'wallets' => $wallets,
            'filters' => ['search' => $search, 'type' => $type],
            'totals' => $totals,
        ]);
    }

    public function show(Request $request, Wallet $wallet): View
    {
        $this->authorize('view', $wallet);

        $wallet->load('user:id,name,email,phone');

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(20);

        $summary = [
            'credits' => $wallet->transactions()->where('direction', WalletTransaction::DIRECTION_CREDIT)->count(),
            'debits' => $wallet->transactions()->where('direction', WalletTransaction::DIRECTION_DEBIT)->count(),
        ];

        return view('wallets.show', compact('wallet', 'transactions', 'summary'));
    }
}
