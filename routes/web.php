<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
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
*/
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
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

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

/*
|--------------------------------------------------------------------------
| Authenticated application
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // In-app camera QR scanner. Decoding a code forwards to verify.show, which
    // records the scan and (for approved users) credits reward points.
    Route::view('scan', 'scan')->name('scan');

    // Catalog
    Route::resource('products', ProductController::class);
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');

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
    Route::post('users/{user}/approve', [UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/revoke-approval', [UserController::class, 'revokeApproval'])->name('users.revoke-approval');
    Route::resource('roles', RoleController::class)->except('show');

    // The signed-in user's own rewards area
    Route::get('my/scans', [MyRewardController::class, 'scans'])->name('my.scans');
    Route::get('my/rewards', [MyRewardController::class, 'wallet'])->name('my.rewards');
    Route::post('my/rewards/payout', [MyRewardController::class, 'requestPayout'])->name('my.rewards.payout');
});
