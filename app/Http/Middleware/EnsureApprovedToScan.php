<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the product-verification (QR scan) flow behind admin approval. The
 * `auth` middleware runs first, so anonymous visitors are already sent to login.
 * A signed-in but not-yet-approved account may view the rest of the app, but is
 * bounced back to the dashboard with a notice instead of being allowed to scan.
 */
class EnsureApprovedToScan
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isApproved()) {
            return redirect()->route('dashboard')->with(
                'info',
                'Your account is awaiting admin approval. You can view the app, but scanning is enabled once you are approved.',
            );
        }

        return $next($request);
    }
}
