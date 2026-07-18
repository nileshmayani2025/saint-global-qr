@extends('layouts.guest')
@section('title', 'Verify OTP')

@php
    use App\Services\Auth\OtpService;
    use App\Support\Phone;

    $length = OtpService::LENGTH;
    $registering = $intent === OtpService::INTENT_REGISTER;
@endphp

@section('content')
    <h1 class="font-display text-2xl font-bold">Verify your number</h1>
    <p class="text-[var(--muted)] mt-1 text-sm">
        Enter the {{ $length }}-digit code for
        <span class="font-semibold text-[var(--fg)] whitespace-nowrap">+91 {{ Phone::format($phone) }}</span>
    </p>

    <form method="POST" action="{{ route('otp.verify') }}" class="mt-7"
          x-data="{
              digits: @js(str_split(str_pad($prefill, $length, '0', STR_PAD_LEFT))),
              set(i, e) {
                  const v = (e.target.value || '').replace(/\D/g, '').slice(-1);
                  this.digits[i] = v;
                  e.target.value = v;
                  if (v && i < {{ $length - 1 }}) this.$refs['d' + (i + 1)].focus();
              },
              back(i, e) {
                  if (e.target.value || i === 0) return;
                  this.digits[i - 1] = '';
                  this.$refs['d' + (i - 1)].value = '';
                  this.$refs['d' + (i - 1)].focus();
              },
          }">
        @csrf
        {{-- Server-rendered value keeps the form submittable if Alpine never
             boots; the binding takes over as soon as it does. --}}
        <input type="hidden" name="code" value="{{ $prefill }}" :value="digits.join('')">

        <div class="flex justify-center gap-3">
            @for ($i = 0; $i < $length; $i++)
                <input type="text" inputmode="numeric" maxlength="1" autocomplete="one-time-code"
                       aria-label="Digit {{ $i + 1 }}"
                       value="{{ substr($prefill, $i, 1) }}"
                       x-ref="d{{ $i }}"
                       @input="set({{ $i }}, $event)"
                       @keydown.backspace="back({{ $i }}, $event)"
                       @focus="$event.target.select()"
                       class="w-14 h-16 text-center text-2xl font-bold lux-field focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            @endfor
        </div>

        @error('code')
            <p class="mt-3 text-sm text-rose-500 text-center">{{ $message }}</p>
        @enderror

        <p class="mt-3 text-xs text-center text-[var(--muted)]">
            Code auto-filled — just tap {{ $registering ? 'Create account' : 'Verify & sign in' }}.
        </p>

        <button class="mt-6 w-full rounded-lg lux-btn text-white font-medium py-2.5 transition">
            {{ $registering ? 'Create account' : 'Verify & sign in' }}
        </button>
    </form>

    <div class="mt-6 flex items-center justify-center gap-4 text-sm">
        <a href="{{ route($registering ? 'register' : 'app.login') }}" class="text-slate-500 dark:text-slate-400 hover:text-brand-600">
            Change number
        </a>
        <span class="text-slate-300 dark:text-slate-700">·</span>
        <form method="POST" action="{{ route('otp.resend') }}">
            @csrf
            <button class="text-brand-600 font-medium hover:underline">Resend code</button>
        </form>
    </div>
@endsection
