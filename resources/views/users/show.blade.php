@extends('layouts.app')
@section('title', $userModel->name)

@section('content')
    <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-brand-600">&larr; Back to users</a>

    <div class="mt-4 grid lg:grid-cols-3 gap-6">
        <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 text-center">
            <div class="w-20 h-20 mx-auto rounded-full bg-brand-600 text-white grid place-items-center text-2xl font-bold">{{ strtoupper(substr($userModel->name, 0, 1)) }}</div>
            <h2 class="mt-4 text-lg font-bold">{{ $userModel->name }}</h2>
            <p class="text-slate-500 text-sm">{{ $userModel->email }}</p>
            <div class="mt-3 flex flex-wrap justify-center gap-1">
                @foreach ($userModel->getRoleNames() as $role)
                    <span class="text-xs px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800">{{ $role }}</span>
                @endforeach
            </div>
            @can('users.update')<a href="{{ route('users.edit', $userModel) }}" class="mt-4 inline-block text-sm text-brand-600 hover:underline">Edit user</a>@endcan
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="font-semibold mb-4">Account</h3>
                <dl class="grid sm:grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-slate-400">Phone</dt><dd class="font-medium">{{ $userModel->phone ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Status</dt><dd><x-badge :status="$userModel->status" /></dd></div>
                    <div><dt class="text-slate-400">Company</dt><dd class="font-medium">{{ $userModel->company?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-400">Approved</dt><dd class="font-medium">{{ $userModel->isApproved() ? optional($userModel->approved_at)->format('d M Y') : 'Pending' }}</dd></div>
                    <div><dt class="text-slate-400">Last login</dt><dd class="font-medium">{{ optional($userModel->last_login_at)->diffForHumans() ?? 'Never' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6">
                <h3 class="font-semibold mb-4">Wallets</h3>
                <div class="space-y-2 text-sm">
                    @forelse ($userModel->wallets as $wallet)
                        <div class="flex justify-between">
                            <span class="capitalize">{{ $wallet->type }}</span>
                            <span class="font-semibold">₹{{ number_format((float) $wallet->balance, 2) }}</span>
                        </div>
                    @empty
                        <p class="text-slate-400">No wallets.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
