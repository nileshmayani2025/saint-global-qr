<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Verification\ScanContext;
use App\Services\Verification\VerificationResult;
use App\Services\Verification\VerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public JSON verification endpoints consumed by the Capacitor mobile app and
 * third-party integrations. No auth; rate limited by the `verify` limiter.
 */
class VerificationController extends Controller
{
    public function __construct(private readonly VerificationService $service)
    {
    }

    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:191'],
            'device_id' => ['nullable', 'string', 'max:100'],
            'lat' => ['nullable', 'numeric', 'between:-90,90'],
            'lng' => ['nullable', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
        ]);

        $result = $this->service->verify(new ScanContext(
            rawCode: trim($validated['code']),
            userId: $request->user()?->id,
            deviceId: $validated['device_id'] ?? null,
            latitude: isset($validated['lat']) ? (float) $validated['lat'] : null,
            longitude: isset($validated['lng']) ? (float) $validated['lng'] : null,
            accuracy: isset($validated['accuracy']) ? (float) $validated['accuracy'] : null,
        ));

        return response()->json([
            'data' => $result->toArray(),
        ], $this->statusFor($result));
    }

    public function show(Request $request, string $code): JsonResponse
    {
        $result = $this->service->verify(new ScanContext(
            rawCode: trim($code),
            userId: $request->user()?->id,
            deviceId: $request->query('device_id'),
        ));

        return response()->json(['data' => $result->toArray()], $this->statusFor($result));
    }

    private function statusFor(VerificationResult $result): int
    {
        return match ($result->result) {
            'valid', 'duplicate' => 200,
            'blocked' => 423,
            'expired' => 410,
            default => 404,
        };
    }
}
