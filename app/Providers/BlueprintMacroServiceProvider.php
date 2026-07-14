<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

/**
 * Registers reusable schema macros so every business table shares an identical,
 * audit-ready column contract without copy/paste in each migration.
 */
class BlueprintMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Public UUID identifier, placed right after id().
        Blueprint::macro('publicId', function (): void {
            /** @var Blueprint $this */
            $this->uuid('uuid')->unique();
        });

        // created_by / updated_by / deleted_by (FK to users) + soft delete column.
        Blueprint::macro('auditColumns', function (): void {
            /** @var Blueprint $this */
            $this->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $this->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $this->softDeletes();
        });
    }
}
