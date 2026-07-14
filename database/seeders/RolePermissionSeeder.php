<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Support\Access\AccessControl;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            $catalogue = AccessControl::permissions();

            foreach ($catalogue as $permission) {
                Permission::findOrCreate($permission, 'web');
            }

            // Prune permissions that are no longer part of the catalogue.
            Permission::query()->whereNotIn('name', $catalogue)->delete();

            foreach (AccessControl::roles() as $roleName) {
                Role::findOrCreate($roleName, 'web');
            }

            foreach (AccessControl::rolePermissions() as $roleName => $permissions) {
                Role::findByName($roleName, 'web')->syncPermissions($permissions);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
