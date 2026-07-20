<?php

declare(strict_types=1);

namespace App\Support\Access;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Brings the permissions table up to date with AccessControl's catalogue.
 *
 * The Roles screen lists permissions from the database, and Spatie can only
 * assign a permission row that exists — so a catalogue entry that was never
 * inserted is invisible and ungrantable. That is what happens after a deploy
 * that adds modules but never runs a sync: the module is live, but nobody can
 * be given access to it.
 *
 * Purely additive. Nothing is deleted and no existing role assignment is
 * touched, which is what makes it safe to run against a live site (unlike
 * RolePermissionSeeder, whose syncPermissions() resets roles to defaults).
 */
final class PermissionSynchroniser
{
    /**
     * Catalogue entries with no row in the database yet.
     *
     * @return list<string>
     */
    public static function missing(): array
    {
        return array_values(array_diff(
            AccessControl::permissions(),
            Permission::query()->pluck('name')->all(),
        ));
    }

    /**
     * Create the missing permissions.
     *
     * Granting is off by default and deliberately so: a new module must not
     * silently widen anyone's access. The permissions become visible on the
     * Roles screen and an admin decides who gets them. Pass $grant only for a
     * fresh install that needs sensible starting defaults.
     *
     * @return array{created: list<string>, granted: array<string, int>}
     */
    public static function sync(bool $grant = false): array
    {
        $missing = self::missing();
        $granted = [];

        if ($missing === []) {
            return ['created' => [], 'granted' => []];
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () use ($missing, $grant, &$granted): void {
            foreach ($missing as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            foreach (AccessControl::roles() as $role) {
                Role::findOrCreate($role, 'web');
            }

            if (! $grant) {
                return;
            }

            foreach (AccessControl::rolePermissions() as $roleName => $defaults) {
                // Only the brand-new ones, and via givePermissionTo so the
                // role keeps everything it already had.
                $new = array_values(array_intersect($defaults, $missing));

                if ($new === []) {
                    continue;
                }

                Role::findByName($roleName, 'web')->givePermissionTo($new);
                $granted[$roleName] = count($new);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return ['created' => $missing, 'granted' => $granted];
    }
}
