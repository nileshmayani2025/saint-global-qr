<?php

declare(strict_types=1);

namespace App\Services\Audit;

use App\Models\ActivityLog;
use App\Support\RequestContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Central writer for the activity_logs table. Enriches every entry with the
 * current request context (IP / device / browser) and the authenticated causer.
 */
class ActivityLogger
{
    /**
     * Record an activity entry.
     *
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $event,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = [],
        string $logName = 'default',
        ?int $causerId = null,
    ): ActivityLog {
        $context = $this->context();

        return ActivityLog::create([
            'log_name' => $logName,
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_id' => $causerId ?? Auth::id(),
            'properties' => $properties === [] ? null : $properties,
            'ip_address' => $context->ipAddress,
            'user_agent' => $context->userAgent,
            'browser' => $context->browser,
            'device' => $context->device,
            'method' => $context->method,
            'url' => $context->url,
        ]);
    }

    /**
     * Convenience helper for model lifecycle events with a change diff.
     *
     * @param  array<string, mixed>  $old
     * @param  array<string, mixed>  $attributes
     */
    public function logModelChange(string $event, Model $subject, array $old = [], array $attributes = []): ActivityLog
    {
        $properties = [];

        if ($old !== []) {
            $properties['old'] = $old;
        }

        if ($attributes !== []) {
            $properties['attributes'] = $attributes;
        }

        $name = class_basename($subject);

        return $this->log(
            event: $event,
            subject: $subject,
            description: "{$name} {$event}",
            properties: $properties,
            logName: 'model',
        );
    }

    private function context(): RequestContext
    {
        if (app()->bound(RequestContext::class)) {
            return app(RequestContext::class);
        }

        return RequestContext::empty();
    }
}
