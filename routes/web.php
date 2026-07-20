<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BusinessCard\BusinessCardController;
use App\Http\Controllers\BusinessCard\MyBusinessCardController;
use App\Http\Controllers\BusinessCard\PublicCardController;
use App\Http\Controllers\Consumer\MyLeadController;
use App\Http\Controllers\Consumer\MyVideoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Geo\CityController;
use App\Http\Controllers\Geo\CountryController;
use App\Http\Controllers\Geo\StateController;
use App\Http\Controllers\Lead\LeadController;
use App\Http\Controllers\Marketing\BannerController;
use App\Http\Controllers\Notification\MyNotificationController;
use App\Http\Controllers\Notification\PushNotificationController;
use App\Http\Controllers\Notification\PushSubscriptionController;
use App\Http\Controllers\Product\BatchController;
use App\Http\Controllers\Product\BrandController;
use App\Http\Controllers\Product\CategoryController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\TradingVideoController;
use App\Http\Controllers\ProfileController;
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
| Two separate front doors:
|   - The web admin panel (/login) signs in with email + password.
|   - The consumer app (/app/login, the PWA) signs in with a mobile number and
|     an OTP, and self-registration continues on the shared OTP screen.
*/
Route::middleware('guest')->group(function () {
    // Admin / staff panel — credential sign-in.
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'authenticate'])->middleware('throttle:login');

    // Consumer app — mobile number + OTP.
    Route::get('app/login', [LoginController::class, 'showApp'])->name('app.login');
    Route::post('app/login', [LoginController::class, 'requestOtp'])->name('app.login.otp')->middleware('throttle:otp');

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
| Public digital business cards
|--------------------------------------------------------------------------
| Open by design — the owner hands the link to a customer. The slug is a
| 20-character random string so cards cannot be guessed or enumerated, and
| PublicCardController 404s anything inactive.
*/
Route::get('c/{slug}', [PublicCardController::class, 'show'])->name('card.show');
Route::get('c/{slug}/vcf', [PublicCardController::class, 'vcard'])->name('card.vcf');
Route::get('c/{slug}/qr.png', [PublicCardController::class, 'qr'])->name('card.qr');

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

/*
|--------------------------------------------------------------------------
| Firebase messaging service worker
|--------------------------------------------------------------------------
| Served through Laravel rather than as a static public/ file so the Firebase
| web config comes from .env instead of being hard-coded. A service worker can
| only control pages at or below its own path, and this route sits at the app
| root — on live that is /qr/public/, which is exactly the app's scope.
*/
Route::get('firebase-messaging-sw.js', function () {
    $config = array_filter(config('services.firebase.web', []));

    return response()
        ->view('push.service-worker', ['config' => $config])
        ->header('Content-Type', 'application/javascript')
        ->header('Service-Worker-Allowed', '/')
        ->header('Cache-Control', 'no-cache');
})->name('push.service-worker');

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
    Route::resource('trading-videos', TradingVideoController::class)->except('show');

    // Home-carousel banners for the consumer app
    Route::resource('banners', BannerController::class)->except('show');

    // Geography masters — global reference data used by user addresses.
    Route::resource('countries', CountryController::class)->except('show');
    Route::resource('states', StateController::class)->except('show');
    Route::resource('cities', CityController::class)->except('show');

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

    // Push notification campaigns (admin)
    Route::resource('push-notifications', PushNotificationController::class);
    Route::post('push-notifications/{pushNotification}/send', [PushNotificationController::class, 'send'])
        ->name('push-notifications.send');

    // Lead generation
    Route::resource('leads', LeadController::class);

    // Digital business cards — admin oversight of everyone's card.
    Route::get('business-cards', [BusinessCardController::class, 'index'])->name('business-cards.index');
    Route::post('business-cards/{businessCard}/toggle', [BusinessCardController::class, 'toggle'])->name('business-cards.toggle');
    Route::post('business-cards/{businessCard}/regenerate', [BusinessCardController::class, 'regenerate'])->name('business-cards.regenerate');
    Route::delete('business-cards/{businessCard}', [BusinessCardController::class, 'destroy'])->name('business-cards.destroy');

    // Users + Roles
    Route::resource('users', UserController::class);
    Route::resource('roles', RoleController::class)->except('show');
    // Creates catalogue permissions missing from the database, so a deploy that
    // added modules can be completed without shell access.
    Route::post('roles/sync-permissions', [RoleController::class, 'syncPermissions'])
        ->name('roles.sync-permissions');

    // The signed-in user's own profile. Open to every authenticated account —
    // it only ever reads and writes the caller's own row, so it is deliberately
    // not gated behind the users.* permissions.
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/support-contacts', [ProfileController::class, 'updateSupportContacts'])
        ->name('profile.support-contacts');

    // App-facing screens: watching product videos and capturing leads are open
    // to every signed-in user, unlike the staff modules behind
    // trading-videos.* and leads.* which manage the same data.
    Route::get('my/videos', [MyVideoController::class, 'index'])->name('my.videos');
    Route::get('my/leads', [MyLeadController::class, 'index'])->name('my.leads.index');
    Route::get('my/leads/create', [MyLeadController::class, 'create'])->name('my.leads.create');
    Route::post('my/leads', [MyLeadController::class, 'store'])->name('my.leads.store');

    // The signed-in user's own digital business card.
    Route::get('my/business-card', [MyBusinessCardController::class, 'edit'])->name('my.business-card.edit');
    Route::put('my/business-card', [MyBusinessCardController::class, 'update'])->name('my.business-card.update');
    Route::post('my/business-card/regenerate', [MyBusinessCardController::class, 'regenerate'])->name('my.business-card.regenerate');

    // Dismisses the welcome video popup. Every signed-in user needs this — it
    // only writes a watermark on their own row — so it lives here rather than
    // behind the trading-videos.* admin permissions.
    Route::post('my/trading-video/{tradingVideo}/seen', [TradingVideoController::class, 'markSeen'])
        ->name('my.trading-video.seen');

    // The signed-in user's notification inbox (the topbar bell) and the FCM
    // token registration the browser calls after permission is granted.
    Route::get('my/notifications', [MyNotificationController::class, 'index'])->name('my.notifications');
    Route::post('my/notifications/read-all', [MyNotificationController::class, 'readAll'])->name('my.notifications.read-all');
    Route::get('my/notifications/{recipient}', [MyNotificationController::class, 'read'])->name('my.notifications.read');
    Route::post('push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::post('push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    // The signed-in user's own rewards area
    Route::get('my/scans', [MyRewardController::class, 'scans'])->name('my.scans');
    Route::get('my/rewards', [MyRewardController::class, 'wallet'])->name('my.rewards');
    Route::post('my/rewards/payout', [MyRewardController::class, 'requestPayout'])->name('my.rewards.payout');
});
