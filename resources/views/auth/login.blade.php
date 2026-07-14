@extends('layouts.guest')
@section('title', 'Sign in')

@section('content')
    <img src="/images/logo.png" alt="Saint Globe" class="lg:hidden w-16 h-16 rounded-2xl shadow-md mb-5">
    <h1 class="text-2xl font-bold">Welcome back</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1">Sign in to your Saint Globe account.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5">Password</label>
            <input type="password" name="password" required
                   class="w-full rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Remember me
        </label>
        <button class="w-full rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium py-2.5 transition">Sign in</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        No account? <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:underline">Create one</a>
    </p>

    <div class="mt-8 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 text-sm">
        <p class="font-medium mb-2">Demo credentials</p>
        <ul class="space-y-1 text-slate-500 dark:text-slate-400">
            <li>Super Admin — <code>admin@test</code> / <code>password</code></li>
            <li>Company — <code>company@test</code> / <code>password</code></li>
        </ul>
    </div>
@endsection
