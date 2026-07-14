@extends('layouts.guest')
@section('title', 'Verify a product')

@section('content')
    <div class="text-center">
        <img src="/images/logo.png" alt="Saint Globe" class="w-16 h-16 mx-auto rounded-2xl shadow-md">
        <h1 class="mt-4 text-2xl font-bold">Verify your product</h1>
        <p class="text-slate-500 dark:text-slate-400 mt-1">Enter the code printed on the QR label to check authenticity.</p>
    </div>

    <form method="POST" action="{{ route('verify.submit') }}" class="mt-8 space-y-4">
        @csrf
        <input name="code" value="{{ old('code') }}" required autofocus placeholder="Enter or paste the product code"
               class="w-full lux-field px-4 py-3 text-center focus:ring-2 focus:ring-brand-500 outline-none">
        <button class="w-full rounded-lg lux-btn text-white font-medium py-3">Verify now</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500 dark:text-slate-400">
        <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:underline">Sign in</a> to earn reward points.
    </p>
@endsection
