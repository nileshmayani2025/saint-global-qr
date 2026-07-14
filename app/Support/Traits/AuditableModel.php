<?php

declare(strict_types=1);

namespace App\Support\Traits;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Convenience bundle applied to every business model. Combines the public UUID,
 * the created_by/updated_by/deleted_by blame stamps, automatic activity logging,
 * soft deletes and factory support.
 *
 * Laravel boots nested trait `boot{Trait}` hooks via class_uses_recursive(), so
 * HasUuid, Blameable and LogsActivity all initialise correctly through this bundle.
 */
trait AuditableModel
{
    use Blameable;
    use HasFactory;
    use HasUuid;
    use LogsActivity;
    use SoftDeletes;
}
