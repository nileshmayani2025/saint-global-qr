@extends('layouts.guest')
@section('title', 'Create account')

@section('content')
    <h1 class="font-display text-2xl font-bold">Create your account</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1">Verify your mobile number and start scanning right away.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium mb-1.5">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                   autocomplete="name"
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            @error('name')
                <p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium mb-1.5">Mobile number</label>
            {{-- .lux-field is width:100%, so the +91 goes inside one field box
                 rather than beside it as a sibling that would push the input out. --}}
            <div class="flex items-center lux-field focus-within:border-brand-500 focus-within:shadow-[0_0_0_4px_var(--ring)]">
                <span class="pl-3.5 pr-2 text-sm text-[var(--muted)] select-none shrink-0">+91</span>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required
                       inputmode="numeric" autocomplete="tel" maxlength="10" placeholder="98765 43210"
                       class="flex-1 min-w-0 bg-transparent border-0 outline-none py-2.5 pr-3.5 tracking-wider text-[var(--text)] placeholder:text-[var(--muted)]">
            </div>
            @error('phone')
                <p class="mt-1.5 text-sm text-rose-500">{{ $message }}</p>
            @enderror
        </div>
        <label class="flex items-start gap-2 text-sm">
            <input type="checkbox" name="terms" value="1" required class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            I agree to the terms of service
        </label>
        @error('terms')
            <p class="text-sm text-rose-500">{{ $message }}</p>
        @enderror
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Send OTP</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        Already registered? <a href="{{ route('app.login') }}" class="text-brand-600 font-medium hover:underline">Sign in</a>
    </p>
@endsection
