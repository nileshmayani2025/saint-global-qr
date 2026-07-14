<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — public product verification (mobile app / partners)
|--------------------------------------------------------------------------
| Prefixed with /api by bootstrap/app.php, so these resolve under /api/v1.
*/
Route::prefix('v1')->middleware('throttle:verify')->group(function () {
    Route::post('verify', [VerificationController::class, 'verify']);
    Route::get('verify/{code}', [VerificationController::class, 'show'])->where('code', '.*');
});
