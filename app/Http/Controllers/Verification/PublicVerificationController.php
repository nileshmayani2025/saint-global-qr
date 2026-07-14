<?php

declare(strict_types=1);

namespace App\Http\Controllers\Verification;

use App\Http\Controllers\Controller;
use App\Services\Verification\ScanContext;
use App\Services\Verification\VerificationResult;
use App\Services\Verification\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    public function __construct(private readonly VerificationService $service)
    {
    }

    public function form(): View
    {
        return view('verify.form');
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:191'],
        ]);

        return redirect()->route('verify.show', ['code' => trim($validated['code'])]);
    }

    /**
     * The URL encoded inside every QR code. Performs the verification (idempotent
     * — a repeat scan is reported as a duplicate) and renders the result page.
     */
    public function show(Request $request, string $code): View
    {
        $result = $this->service->verify($this->context($request, $code));

        return view('verify.result', [
            'result' => $result,
            'code' => $code,
        ]);
    }

    private function context(Request $request, string $code): ScanContext
    {
        return new ScanContext(
            rawCode: $code,
            userId: $request->user()?->id,
            deviceId: $this->deviceId($request),
            latitude: $request->filled('lat') ? (float) $request->input('lat') : null,
            longitude: $request->filled('lng') ? (float) $request->input('lng') : null,
            accuracy: $request->filled('accuracy') ? (float) $request->input('accuracy') : null,
        );
    }

    /**
     * Stable per-browser device identifier stored in a long-lived cookie so the
     * fraud engine can track scan velocity without personal data.
     */
    private function deviceId(Request $request): string
    {
        $deviceId = $request->cookie('device_id');

        if (! is_string($deviceId) || $deviceId === '') {
            $deviceId = (string) \Illuminate\Support\Str::uuid();
            cookie()->queue(cookie('device_id', $deviceId, 60 * 24 * 365));
        }

        return $deviceId;
    }
}
