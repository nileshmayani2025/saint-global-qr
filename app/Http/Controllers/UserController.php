<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\User\UserRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\UserManagement\UserService;
use App\Support\Access\AccessControl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = $this->filters($request);

        return view('users.index', [
            'users' => $this->service->paginate($filters, (int) $request->integer('per_page', 15)),
            'filters' => $filters,
            'roles' => AccessControl::roles(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.form', [
            'userModel' => new User(['status' => 'active']),
            'assignedRoles' => [],
            'roles' => $this->assignableRoles(),
            'companies' => $this->companies(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $this->service->create($request->validated(), $request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load('roles:id,name', 'company:id,name', 'wallets');

        return view('users.show', ['userModel' => $user]);
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.form', [
            'userModel' => $user,
            'assignedRoles' => $user->getRoleNames()->all(),
            'roles' => $this->assignableRoles(),
            'companies' => $this->companies(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $this->service->update($user, $request->validated(), $request->input('roles', []));

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $this->service->delete($user);

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (! $user->isApproved()) {
            $user->forceFill([
                'approved_at' => now(),
                'approved_by' => $request->user()->id,
            ])->save();
        }

        return redirect()->back()->with('success', "{$user->name} approved — they can now scan & verify products.");
    }

    public function revokeApproval(User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $user->forceFill([
            'approved_at' => null,
            'approved_by' => null,
        ])->save();

        return redirect()->back()->with('success', "{$user->name}'s scanning access has been revoked.");
    }

    /**
     * @return array<string, mixed>
     */
    private function filters(Request $request): array
    {
        $filters = $request->only(['search', 'status', 'role']);

        if ($request->user()->company_id !== null) {
            $filters['company_id'] = $request->user()->company_id;
        }

        return array_filter($filters, static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return list<string>
     */
    private function assignableRoles(): array
    {
        $roles = AccessControl::roles();

        if (! request()->user()->hasRole(AccessControl::ROLE_SUPER_ADMIN)) {
            $roles = array_values(array_filter($roles, static fn (string $r): bool => $r !== AccessControl::ROLE_SUPER_ADMIN));
        }

        return $roles;
    }

    private function companies()
    {
        // Company-scoped admins don't choose a company; only super-admins do.
        if (request()->user()->company_id !== null) {
            return collect();
        }

        return Company::query()->orderBy('name')->get(['id', 'name']);
    }
}
