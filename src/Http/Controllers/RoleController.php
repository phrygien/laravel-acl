<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Mecene\LaravelAcl\Models\Role;
use Mecene\LaravelAcl\Models\Permission;

class RoleController extends Controller
{
    // ─── Index ────────────────────────────────────────────────

    public function index(): \Illuminate\View\View
    {
        $roles = Role::withCount('users')
            ->with('permissions')
            ->get();

        // ← manquait dans la version précédente
        $permissions = Permission::orderBy('slug')->get();

        return view('acl::roles.index', compact('roles', 'permissions'));
    }

    // ─── Store ────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'          => 'required|string|unique:roles,name',
            'slug'          => 'required|string|unique:roles,slug',
            'description'   => 'nullable|string',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'description' => $data['description'] ?? null,
        ]);

        if (! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        AuditLog::record(
            action:     AuditLog::ACTION_CREATE_ROLE,
            properties: ['role' => $role->slug],
        );

        return response()->json([
            'message' => "Rôle \"{$role->name}\" créé avec succès.",
            'role'    => $role->load('permissions'),
        ]);
    }

    // ─── Update ───────────────────────────────────────────────

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'name'        => "required|string|unique:roles,name,{$role->id}",
            'slug'        => "required|string|unique:roles,slug,{$role->id}",
            'description' => 'nullable|string',
        ]);

        $role->update($data);

        AuditLog::record(
            action:     AuditLog::ACTION_UPDATE_ROLE,
            properties: ['role' => $role->slug],
        );

        return response()->json([
            'message' => "Rôle \"{$role->name}\" mis à jour.",
            'role'    => $role,
        ]);
    }

    // ─── Destroy ──────────────────────────────────────────────

    public function destroy(Role $role): JsonResponse
    {
        AuditLog::record(
            action:     AuditLog::ACTION_DELETE_ROLE,
            properties: [
                'role'          => $role->slug,
                'users_affected'=> $role->users_count ?? 0,
            ],
        );

        $role->delete();

        return response()->json([
            'message' => "Rôle \"{$role->name}\" supprimé.",
        ]);
    }

    // ─── Sync permissions ─────────────────────────────────────

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions'   => 'required|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $before = $role->permissions->pluck('slug')->toArray();
        $role->permissions()->sync($data['permissions']);
        $after  = $role->fresh()->permissions->pluck('slug')->toArray();

        AuditLog::record(
            action:     AuditLog::ACTION_SYNC_PERMS,
            properties: [
                'role'    => $role->slug,
                'before'  => $before,
                'after'   => $after,
                'added'   => array_values(array_diff($after, $before)),
                'removed' => array_values(array_diff($before, $after)),
            ],
        );

        return response()->json([
            'message'     => 'Permissions synchronisées.',
            'permissions' => $role->permissions,
        ]);
    }
}