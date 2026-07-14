<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\RequestContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Binds a RequestContext snapshot into the container for the lifetime of the
 * request so downstream services (activity logger, fraud service) can capture
 * IP / device / browser without depending on the Request object directly.
 */
class CaptureRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->instance(RequestContext::class, RequestContext::fromRequest($request));

        return $next($request);
    }
}
