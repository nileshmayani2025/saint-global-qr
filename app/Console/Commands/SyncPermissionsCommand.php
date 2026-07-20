<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Access\AccessControl;
use App\Support\Access\PermissionSynchroniser;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

/**
 * The safe way to roll new permissions onto a live install.
 *
 * RolePermissionSeeder is destructive by design: it prunes anything outside the
 * catalogue and calls syncPermissions(), which resets every role to its default
 * set. That is right for a fresh database and wrong for a running site where an
 * admin has tuned roles by hand. This only ever adds.
 *
 * The same logic is reachable from the Roles screen, so an admin without shell
 * access is not stuck.
 */
class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync
                            {--grant : Also grant the new permissions to their default roles (off by default)}
                            {--dry-run : Show what would change without writing}';

    protected $description = 'Add missing catalogue permissions. Grants nothing unless --grant is passed';

    public function handle(): int
    {
        $catalogue = AccessControl::permissions();
        $missing = PermissionSynchroniser::missing();

        $this->info(sprintf('Catalogue: %d · in database: %d', count($catalogue), Permission::query()->count()));

        if ($missing === []) {
            $this->line('  Nothing to add — every catalogue permission already exists.');

            return self::SUCCESS;
        }

        $this->line('  To add: '.implode(', ', $missing));

        if ($this->option('dry-run')) {
            $this->comment('Dry run — nothing was written.');

            return self::SUCCESS;
        }

        $result = PermissionSynchroniser::sync(grant: (bool) $this->option('grant'));

        foreach ($result['granted'] as $role => $count) {
            $this->line("  {$role}: +{$count}");
        }

        $this->info(sprintf('Created %d permission(s).', count($result['created'])));

        if ($result['granted'] === []) {
            $this->comment('Granted to nobody — assign them from the Roles screen.');
        }

        return self::SUCCESS;
    }
}
