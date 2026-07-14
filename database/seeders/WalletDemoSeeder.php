<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Company;
use App\Models\QrCode;
use App\Models\User;
use App\Services\Verification\ScanContext;
use App\Services\Verification\VerificationService;
use App\Support\Access\AccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Creates a demo consumer and runs a handful of reward-earning verifications so
 * the Wallets screen shows real balances and a ledger. Idempotent and safe to
 * run on an existing database (does not wipe anything).
 */
class WalletDemoSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->first();

        if ($company === null) {
            return;
        }

        $consumer = User::query()->firstOrCreate(
            ['email' => 'consumer@saintglobal.test'],
            [
                'name' => 'Rajesh Karigar',
                'phone' => '9812345678',
                'password' => Hash::make('password'),
                'company_id' => $company->id,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );

        if (! $consumer->hasRole(AccessControl::ROLE_KARIGAR)) {
            $consumer->assignRole(AccessControl::ROLE_KARIGAR);
        }

        // Attribute a few fresh (unverified) codes to this consumer → credits wallet.
        $codes = QrCode::query()
            ->where('company_id', $company->id)
            ->where('status', QrCode::STATUS_GENERATED)
            ->limit(6)
            ->pluck('code');

        if ($codes->isEmpty()) {
            return;
        }

        Auth::login($consumer);
        $service = app(VerificationService::class);

        foreach ($codes as $code) {
            $service->verify(new ScanContext(
                rawCode: $code,
                userId: $consumer->id,
                deviceId: 'seed-consumer-device',
                latitude: 19.0760,
                longitude: 72.8777,
            ));
        }

        Auth::logout();
    }
}
