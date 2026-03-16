<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagedUserRequest;
use App\Http\Requests\Admin\UpdateManagedUserRoleRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::query()
                ->withCount(['providedContents', 'purchases'])
                ->latest()
                ->paginate(15)
                ->through(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'provided_contents_count' => $user->provided_contents_count,
                    'purchases_count' => $user->purchases_count,
                    'created_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
                ]),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => $request->string('role')->toString(),
            'email_verified_at' => now(),
            'password' => $request->string('password')->toString(),
        ]);

        return back()->with('success', 'User has been created.');
    }

    public function updateRole(UpdateManagedUserRoleRequest $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot change your own role.');

        $user->update([
            'role' => $request->string('role')->toString(),
        ]);

        return back()->with('success', 'User role has been updated.');
    }
}
