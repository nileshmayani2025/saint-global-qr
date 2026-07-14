<?php

declare(strict_types=1);

namespace App\Services\Verification;

use App\Models\AccessList;
use App\Models\QrCode;
use App\Models\Scan;

/**
 * Encapsulates the anti-fraud rules applied to every scan: blacklist lookups,
 * GPS-radius enforcement and per-device scan-velocity checks.
 */
class FraudService
{
    private const VELOCITY_WINDOW_MINUTES = 60;
    private const VELOCITY_MAX_DISTINCT_CODES = 20;

    /**
     * True when any identifier in the context is actively blacklisted.
     */
    public function isBlocked(ScanContext $context, ?QrCode $qrCode = null): bool
    {
        $candidates = array_filter([
            AccessList::ENTRY_IP => $context->ipAddress,
            AccessList::ENTRY_DEVICE => $context->deviceId,
            AccessList::ENTRY_USER => $context->userId !== null ? (string) $context->userId : null,
            AccessList::ENTRY_CODE => $qrCode?->code ?? $context->rawCode,
        ]);

        foreach ($candidates as $entryType => $value) {
            $blocked = AccessList::query()
                ->active()
                ->blacklist()
                ->where('entry_type', $entryType)
                ->where('value', $value)
                ->exists();

            if ($blocked) {
                return true;
            }
        }

        return false;
    }

    /**
     * Evaluate soft-fraud signals for a scan that is otherwise valid.
     *
     * @return list<string> human-readable reasons (empty when clean)
     */
    public function assess(ScanContext $context, QrCode $qrCode): array
    {
        $reasons = [];

        if ($this->violatesGeofence($context, $qrCode)) {
            $reasons[] = 'scan_outside_allowed_area';
        }

        if ($this->exceedsDeviceVelocity($context)) {
            $reasons[] = 'device_scan_velocity_exceeded';
        }

        return $reasons;
    }

    private function violatesGeofence(ScanContext $context, QrCode $qrCode): bool
    {
        $settings = (array) ($qrCode->company?->settings ?? []);
        $geo = (array) ($settings['geofence'] ?? []);

        if (empty($geo['enforce'])) {
            return false;
        }

        if ($context->latitude === null || $context->longitude === null) {
            return true; // Geofencing on but no coordinates supplied.
        }

        $centerLat = (float) ($geo['latitude'] ?? 0);
        $centerLng = (float) ($geo['longitude'] ?? 0);
        $radiusKm = (float) ($geo['radius_km'] ?? 0);

        if ($radiusKm <= 0) {
            return false;
        }

        return $this->haversineKm($centerLat, $centerLng, $context->latitude, $context->longitude) > $radiusKm;
    }

    private function exceedsDeviceVelocity(ScanContext $context): bool
    {
        if ($context->deviceId === null) {
            return false;
        }

        $distinctCodes = Scan::query()
            ->where('device_id', $context->deviceId)
            ->where('created_at', '>=', now()->subMinutes(self::VELOCITY_WINDOW_MINUTES))
            ->distinct('raw_code')
            ->count('raw_code');

        return $distinctCodes >= self::VELOCITY_MAX_DISTINCT_CODES;
    }

    /**
     * Great-circle distance between two points in kilometres.
     */
    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * asin(min(1.0, sqrt($a)));
    }
}
