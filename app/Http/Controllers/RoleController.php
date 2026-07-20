<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Support\Access\PermissionSynchroniser;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $search = trim((string) $request->string('search'));

        $roles = Role::query()
            ->withCount(['permissions', 'users'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('roles.index', [
            'roles' => $roles,
            // A deploy that adds modules but never syncs leaves catalogue
            // entries with no row in the permissions table. The Roles screen
            // lists from that table, so those modules are invisible here and
            // cannot be granted to anyone — surface it rather than let an
            // admin wonder why the new module never appears.
            'missingPermissions' => PermissionSynchroniser::missing(),
        ]);
    }

    /**
     * Create any catalogue permissions missing from the database.
     *
     * Purely additive — see PermissionSynchroniser. Exposed here so an admin
     * without shell access is not stuck waiting on a deploy.
     */
    public function syncPermissions(): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $result = PermissionSynchroniser::sync();

        if ($result['created'] === []) {
            return back()->with('info', 'Permissions were already up to date.');
        }

        return back()->with('success', sprintf(
            '%d permission(s) added: %s',
            count($result['created']),
            implode(', ', $result['created']),
        ));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('roles.form', [
            'role' => new Role,
            'grouped' => $this->groupedPermissions(),
            'assigned' => [],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        DB::transaction(function () use ($request): void {
            $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
            $role->syncPermissions($request->input('permissions', []));
        });

        $this->flushPermissionCache();

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('roles.form', [
            'role' => $role,
            'grouped' => $this->groupedPermissions(),
            'assigned' => $role->permissions->pluck('name')->all(),
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $role->syncPermissions($request->input('permissions', []));

        $this->flushPermissionCache();

        return redirect()->route('roles.index')->with('success', 'Role permissions updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();
        $this->flushPermissionCache();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }

    /**
     * All permissions grouped by their module prefix (e.g. "products.*").
     *
     * @return array<string, \Illuminate\Support\Collection>
     */
    private function groupedPermissions(): array
    {
        return Permission::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->groupBy(fn (Permission $p) => \Illuminate\Support\Str::before($p->name, '.'))
            ->sortKeys()
            ->all();
    }

    private function flushPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
