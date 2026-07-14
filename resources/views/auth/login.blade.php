@extends('layouts.guest')
@section('title', 'Sign in')

@section('content')
    <h1 class="font-display text-2xl font-bold">Welcome back</h1>
    <p class="text-[var(--muted)] mt-1">Sign in to your Saint Globe account.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5">Password</label>
            <input type="password" name="password" required
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Remember me
        </label>
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Sign in</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        No account? <a href="{{ route('register') }}" class="text-brand-600 font-medium hover:underline">Create one</a>
    </p>
@endsection
