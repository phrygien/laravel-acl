<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use MecenePhrygien\LaravelAcl\Models\Role;

class UserRoleController extends Controller
{
    private function userModel()
    {
        return app(config('acl.user_model'));
    }

    public function index()
    {
        $users = $this->userModel()
            ->with('roles')
            ->paginate(20);

        $roles = Role::all();

        return view('acl::users.index', compact('users', 'roles'));
    }

    public function assign(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|string|exists:roles,slug',
        ]);

        $user = $this->userModel()->findOrFail($data['user_id']);
        $user->assignRole($data['role']);

        return response()->json(['message' => "Rôle \"{$data['role']}\" affecté."]);
    }

    public function revoke(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'required|string|exists:roles,slug',
        ]);

        $user = $this->userModel()->findOrFail($data['user_id']);
        $user->removeRole($data['role']);

        return response()->json(['message' => "Rôle \"{$data['role']}\" révoqué."]);
    }

    public function sync(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles'   => 'required|array',
            'roles.*' => 'string|exists:roles,slug',
        ]);

        $user = $this->userModel()->findOrFail($data['user_id']);
        $user->syncRoles($data['roles']);

        return response()->json(['message' => 'Rôles synchronisés.']);
    }

    public function affectation()
    {
        $users = $this->userModel()->with('roles')->get();
        $roles = Role::with('permissions')->get();
        return view('acl::affectation.index', compact('users', 'roles'));
    }
}