<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Marketing\BannerController;
use App\Http\Controllers\Product\BatchController;
use App\Http\Controllers\Product\BrandController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Qr\QrCodeController;
use App\Http\Controllers\Rewards\MyRewardController;
use App\Http\Controllers\Rewards\RedemptionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Verification\PublicVerificationController;
use App\Http\Controllers\Wallet\WalletController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Guest authentication
|--------------------------------------------------------------------------
|
| Sign-in and sign-up both take a mobile number, open an OTP challenge, and
| finish on the shared OTP screen (see OtpController).
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'requestOtp'])->middleware('throttle:otp');

    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'requestOtp'])->middleware('throttle:otp');

    Route::get('otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('otp', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('otp/resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:otp');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Public product verification
|--------------------------------------------------------------------------
*/
Route::get('verify', [PublicVerificationController::class, 'form'])->name('verify.form');
Route::post('verify', [PublicVerificationController::class, 'verify'])->name('verify.submit');
Route::get('verify/{code}', [PublicVerificationController::class, 'show'])
    ->where('code', '.*')
    ->name('verify.show');

/*
|--------------------------------------------------------------------------
| Public media
|--------------------------------------------------------------------------
| Streams files from the "public" storage disk through PHP so images work
| even when the OS storage symlink is missing (common on shared / subfolder
| hosting). URL is built with asset('media/...') so it inherits APP_URL's
| base path (e.g. /qr/public) automatically.
*/
Route::get('media/{path}', function (string $path) {
    $disk = Storage::disk('public');

    abort_unless($disk->exists($path), 404);

    return $disk->response($path, null, [
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.*')->name('media');

/*
|--------------------------------------------------------------------------
| PWA manifest
|--------------------------------------------------------------------------
| Built here rather than kept as a static public/manifest.webmanifest so every
| URL derives from APP_URL. On live the app sits in a subdirectory (/qr/public),
| where the hard-coded "/images/pwa-192.png" paths resolved to the domain root
| and 404'd, leaving the installed app with no icon.
*/
Route::get('manifest.webmanifest', function () {
    $icons = collect([192, 512])
        ->crossJoin(['any', 'maskable'])
        ->map(fn (array $pair) => [
            'src' => asset("images/pwa-{$pair[0]}.png"),
            'sizes' => "{$pair[0]}x{$pair[0]}",
            'type' => 'image/png',
            'purpose' => $pair[1],
        ])
        ->all();

    return response()->json([
        'name' => 'Saint Globle Verify',
        'short_name' => 'Saint Globle',
        'description' => 'Verify genuine products and earn reward points.',
        'start_url' => route('verify.form'),
        'scope' => url('/').'/',
        'display' => 'standalone',
        'orientation' => 'portrait',
        'background_color' => '#0f172a',
        'theme_color' => '#2ca0d4',
        'icons' => $icons,
    ], options: JSON_UNESCAPED_SLASHES)->header('Content-Type', 'application/manifest+json');
})->name('manifest');

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

/*
|--------------------------------------------------------------------------
| Authenticated application
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // In-app camera QR scanner. Decoding a code forwards to verify.show, which
    // records the scan and credits reward points.
    Route::view('scan', 'scan')->name('scan');

    // Catalog
    Route::resource('products', ProductController::class);
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');

    // Home-carousel banners for the consumer app
    Route::resource('banners', BannerController::class)->except('show');

    // Batches + QR generation
    Route::resource('batches', BatchController::class);
    Route::post('batches/{batch}/generate-qr', [BatchController::class, 'generateQr'])->name('batches.generate-qr');
    Route::get('batches/{batch}/print', [QrCodeController::class, 'printSheet'])->name('qr-codes.print');

    // QR codes
    Route::get('qr-codes', [QrCodeController::class, 'index'])->name('qr-codes.index');
    Route::get('qr-codes/{qrCode}', [QrCodeController::class, 'show'])->name('qr-codes.show');
    Route::post('qr-codes/{qrCode}/block', [QrCodeController::class, 'block'])->name('qr-codes.block');

    // Wallets
    Route::get('wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('wallets/{wallet}', [WalletController::class, 'show'])->name('wallets.show');

    // Redemptions (admin)
    Route::get('redemptions', [RedemptionController::class, 'index'])->name('redemptions.index');
    Route::get('redemptions/{redemption}', [RedemptionController::class, 'show'])->name('redemptions.show');
    Route::post('redemptions/{redemption}/approve', [RedemptionController::class, 'approve'])->name('redemptions.approve');
    Route::post('redemptions/{redemption}/reject', [RedemptionController::class, 'reject'])->name('redemptions.reject');

    // Users + Roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except('show');

    // The signed-in user's own rewards area
    Route::get('my/scans', [MyRewardController::class, 'scans'])->name('my.scans');
    Route::get('my/rewards', [MyRewardController::class, 'wallet'])->name('my.rewards');
    Route::post('my/rewards/payout', [MyRewardController::class, 'requestPayout'])->name('my.rewards.payout');
});
