@extends('layouts.guest')
@section('title', 'Create account')

@section('content')
    <h1 class="text-2xl font-bold">Create your account</h1>
    <p class="text-slate-500 dark:text-slate-400 mt-1">New accounts can scan once an admin approves them.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1.5">Full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1.5">Phone <span class="text-slate-400">(optional)</span></label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1.5">Password</label>
                <input type="password" name="password" required
                       class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1.5">Confirm</label>
                <input type="password" name="password_confirmation" required
                       class="w-full lux-field px-3.5 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="terms" value="1" required class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
            I agree to the terms of service
        </label>
        <button class="w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">Create account</button>
    </form>

    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
        Already registered? <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:underline">Sign in</a>
    </p>
@endsection
