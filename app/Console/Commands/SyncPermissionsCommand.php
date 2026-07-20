<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Access\AccessControl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The safe way to roll new permissions onto a live install.
 *
 * RolePermissionSeeder is destructive by design: it prunes anything outside the
 * catalogue and calls syncPermissions(), which resets every role to its default
 * set. That is right for a fresh database and wrong for a running site where an
 * admin has tuned roles by hand.
 *
 * This command only ever adds:
 *   - creates catalogue permissions that do not exist yet
 *   - grants the brand-new ones to the roles that should have them by default
 *   - never deletes a permission, never revokes an existing assignment
 */
class SyncPermissionsCommand extends Command
{
    protected $signature = 'permissions:sync
                            {--grant : Also grant the newly created permissions to their default roles}
                            {--dry-run : Show what would change without writing}';

    protected $description = 'Add any missing permissions from the catalogue without resetting existing role assignments';

    public function handle(): int
    {
        $catalogue = AccessControl::permissions();
        $existing = Permission::query()->pluck('name')->all();
        $missing = array_values(array_diff($catalogue, $existing));
        $extra = array_values(array_diff($existing, $catalogue));

        $this->info(sprintf('Catalogue: %d · in database: %d', count($catalogue), count($existing)));

        if ($missing === []) {
            $this->line('  Nothing to add — every catalogue permission already exists.');
        } else {
            $this->line('  To add: '.implode(', ', $missing));
        }

        if ($extra !== []) {
            $this->warn('  Present but not in the catalogue (left untouched): '.implode(', ', $extra));
        }

        if ($this->option('dry-run')) {
            $this->comment('Dry run — nothing was written.');

            return self::SUCCESS;
        }

        if ($missing === [] && ! $this->option('grant')) {
            return self::SUCCESS;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () use ($missing): void {
            foreach ($missing as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            foreach (AccessControl::roles() as $role) {
                Role::findOrCreate($role, 'web');
            }

            if (! $this->option('grant')) {
                return;
            }

            // givePermissionTo adds without touching what a role already holds,
            // unlike syncPermissions which replaces the whole set.
            foreach (AccessControl::rolePermissions() as $roleName => $defaults) {
                $grant = array_intersect($defaults, $missing);

                if ($grant === []) {
                    continue;
                }

                Role::findByName($roleName, 'web')->givePermissionTo($grant);
                $this->line("  {$roleName}: +".count($grant));
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Done. No existing role assignment was changed.');

        return self::SUCCESS;
    }
}
