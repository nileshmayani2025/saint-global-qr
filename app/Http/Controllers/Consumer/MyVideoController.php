<?php

declare(strict_types=1);

namespace App\Http\Controllers\Consumer;

use App\Http\Controllers\Controller;
use App\Models\ProductTradingVideo;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The app's video library — what a karigar sees.
 *
 * Read-only on purpose: the trading-videos.* permissions govern the admin CRUD,
 * while watching is open to every signed-in app user, the same way the welcome
 * popup is.
 */
class MyVideoController extends Controller
{
    public function index(Request $request): View
    {
        $videos = ProductTradingVideo::query()
            ->active()
            ->with('product:id,name,sku')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(12);

        // Opening the library counts as having seen the newest video, so the
        // welcome popup does not reappear on the way back to the home screen.
        $newest = ProductTradingVideo::query()->active()->max('id');
        $user = $request->user();

        if ($newest !== null && (int) $newest > (int) $user->last_seen_trading_video_id) {
            $user->forceFill(['last_seen_trading_video_id' => $newest])->save();
        }

        return view('my.videos', ['videos' => $videos]);
    }
}
