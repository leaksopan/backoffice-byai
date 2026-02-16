<?php

namespace Modules\AdminCenter\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\AdminCenter\Http\Requests\StoreUserRequest;
use Modules\AdminCenter\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;

class AcUsersController
{
    public function index(): View
    {
        $users = User::query()
            ->with('roles')
            ->orderBy('name')
            ->get();

        return view('admincenter::users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('admincenter::users.create', [
            'roles' => $roles,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if ($request->user()?->can('assignments.manage')) {
            $roles = Role::query()->whereIn('id', $validated['roles'] ?? [])->get();
            $user->syncRoles($roles);
        }

        return redirect()
            ->route('ac.users.index')
            ->with('status', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $roles = Role::query()->orderBy('name')->get();

        return view('admincenter::users.edit', [
            'user' => $user->load('roles'),
            'roles' => $roles,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($request->user()?->can('assignments.manage')) {
            $roles = Role::query()->whereIn('id', $validated['roles'] ?? [])->get();
            $user->syncRoles($roles);
        }

        return redirect()
            ->route('ac.users.index')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->withErrors(['user' => 'You cannot delete the currently logged-in user.']);
        }

        if ($user->hasRole('super-admin')) {
            return back()->withErrors(['user' => 'Super admin user cannot be deleted.']);
        }

        $user->delete();

        return redirect()
            ->route('ac.users.index')
            ->with('status', 'User deleted successfully.');
    }
}
