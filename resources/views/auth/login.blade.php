@extends('layouts.guest')
@section('title', 'Panel sign in')

@section('content')
    <h1 class="font-display text-2xl font-bold">Panel sign in</h1>
    <p class="text-[var(--muted)] mt-1">Enter your email and password to continue.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="email" class="block text-sm font-medium mb-1.5">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                   autocomplete="username" placeholder="you@company.com"
                   class="lux-field w-full px-3.5 py-2.5 outline-none text-[var(--text)] placeholder:text-[var(--muted)] focus:border-brand-500 focus:shadow-[0_0_0_4px_var(--ring)]">
            @error('email')
                <p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="password" class="block text-sm font-medium mb-1.5">Password</label>
            <input id="password" type="password" name="password" required
                   autocomplete="current-password" placeholder="••••••••"
                   class="lux-field w-full px-3.5 py-2.5 outline-none text-[var(--text)] placeholder:text-[var(--muted)] focus:border-brand-500 focus:shadow-[0_0_0_4px_var(--ring)]">
            @error('password')
                <p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            Keep me signed in
        </label>
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Sign in</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        Are you a customer? <a href="{{ route('app.login') }}" class="text-brand-600 font-medium hover:underline">Sign in with your mobile number</a>
    </p>
@endsection
