<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['company', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();
        $companies = Company::all();

        return view('users.index', compact('users', 'roles', 'companies'));
    }

    public function create()
    {
        $roles = Role::all();
        $companies = Company::all();
        return view('users.create', compact('roles', 'companies'));
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user = User::create($data);

        // Assign Role
        if ($request->filled('role')) {
            $roleObj = Role::where('slug', $request->role)->first();
            if ($roleObj) {
                $user->roles()->sync([$roleObj->id]);
            }
        }

        return redirect()->route('users.index')->with('success', "User '{$user->name}' created successfully!");
    }

    public function show(User $user)
    {
        $user->load(['company', 'roles']);
        $recentAuditLogs = AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('users.show', compact('user', 'recentAuditLogs'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $companies = Company::all();
        return view('users.edit', compact('user', 'roles', 'companies'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user->update($data);

        // Update Role
        if ($request->filled('role')) {
            $roleObj = Role::where('slug', $request->role)->first();
            if ($roleObj) {
                $user->roles()->sync([$roleObj->id]);
            }
        }

        return redirect()->route('users.show', $user->id)->with('success', "User '{$user->name}' updated successfully!");
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:Active,Inactive,Suspended',
        ]);

        $oldStatus = $user->status;
        $user->update(['status' => $request->status]);

        return back()->with('success', "Status for '{$user->name}' changed from {$oldStatus} to {$user->status}!");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own user account.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'User account deactivated/deleted successfully.');
    }
}
