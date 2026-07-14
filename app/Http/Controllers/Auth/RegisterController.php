<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\Audit\ActivityLogger;
use App\Support\Access\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function show(): View
    {
        return view('auth.register');
    }

    public function register(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->whereNull('deleted_at')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ]);

        $user = new User;
        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            // Belongs to the company so it surfaces in the admin approval list,
            // but stays PENDING (approved_at is null) until an admin approves it.
            'company_id' => Company::query()->orderBy('id')->value('id'),
            'status' => 'active',
            'approved_at' => null,
        ]);
        $user->password = Hash::make($data['password']);
        $user->save();

        // New self-registered accounts are consumers (karigar) by default.
        $user->assignRole(AccessControl::ROLE_KARIGAR);

        $logger->log('register', $user, "{$user->name} registered (pending approval)", logName: 'auth', causerId: $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with(
            'info',
            'Welcome to Saint Global! Your account is pending approval — you can view the app, but scanning is enabled once an admin approves you.',
        );
    }
}
