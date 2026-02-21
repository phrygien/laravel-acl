<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MecenePhrygien\LaravelAcl\Models\Role;
use MecenePhrygien\LaravelAcl\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        return view('acl::roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles,name',
            'slug'        => 'required|string|unique:roles,slug',
            'description' => 'nullable|string',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($data);

        if (! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        return response()->json([
            'message' => "Rôle \"{$role->name}\" créé avec succès.",
            'role'    => $role->load('permissions'),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name'        => "required|string|unique:roles,name,{$role->id}",
            'slug'        => "required|string|unique:roles,slug,{$role->id}",
            'description' => 'nullable|string',
        ]);

        $role->update($data);

        return response()->json([
            'message' => "Rôle \"{$role->name}\" mis à jour.",
            'role'    => $role,
        ]);
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => "Rôle \"{$role->name}\" supprimé."]);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($data['permissions']);

        return response()->json([
            'message'     => 'Permissions synchronisées.',
            'permissions' => $role->permissions,
        ]);
    }
}