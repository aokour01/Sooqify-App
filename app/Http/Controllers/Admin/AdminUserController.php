<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->withCount('ads')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%' . $request->q . '%';

                $query->where(fn ($q) => $q->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->when(in_array($request->role, ['user', 'moderator', 'admin'], true),
                fn ($query) => $query->role($request->role))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        abort_if($user->hasRole('admin'), 403, 'Admin roles cannot be changed here.');

        $data = $request->validate([
            'role' => ['required', Rule::in(['user', 'moderator'])],
        ]);

        $user->syncRoles([$data['role']]);

        return back()->with('status', "{$user->name} is now a {$data['role']}.");
    }
}
