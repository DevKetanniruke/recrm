<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with(['permissions', 'users'])->get();
        $permissionsByModule = Permission::all()->groupBy('module');

        return view('roles.index', compact('roles', 'permissionsByModule'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $slug = Str::slug($request->name);

        $role = Role::create([
            'company_id' => auth()->user()->company_id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
        ]);

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' created successfully!");
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($request->permissions ?? []);

        return back()->with('success', "Permissions updated for role '{$role->name}'!");
    }
}
