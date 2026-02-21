<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MecenePhrygien\LaravelAcl\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('roles')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->slug)[0]);

        return view('acl::permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:permissions,name',
            'slug'        => 'required|string|unique:permissions,slug',
            'description' => 'nullable|string',
            'roles'       => 'nullable|array',
            'roles.*'     => 'exists:roles,id',
        ]);

        $permission = Permission::create($data);

        if (! empty($data['roles'])) {
            $permission->roles()->sync($data['roles']);
        }

        return response()->json([
            'message'    => "Permission \"{$permission->slug}\" créée.",
            'permission' => $permission->load('roles'),
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['message' => "Permission \"{$permission->slug}\" supprimée."]);
    }
}