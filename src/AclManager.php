<?php

namespace MecenePhrygien\LaravelAcl;

use MecenePhrygien\LaravelAcl\Models\Role;
use MecenePhrygien\LaravelAcl\Models\Permission;

class AclManager
{
    public function createRole(string $name, string $slug, string $description = ''): Role
    {
        return Role::firstOrCreate(
            ['slug' => $slug],
            compact('name', 'slug', 'description')
        );
    }

    public function createPermission(string $name, string $slug, string $description = ''): Permission
    {
        return Permission::firstOrCreate(
            ['slug' => $slug],
            compact('name', 'slug', 'description')
        );
    }

    public function assignPermissionToRole(string $roleSlug, string|array $permissionSlugs): void
    {
        $role = Role::where('slug', $roleSlug)->firstOrFail();

        foreach ((array) $permissionSlugs as $slug) {
            $role->givePermission($slug);
        }
    }

    public function getRolesFor(object $user)
    {
        return $user->getCachedRoles();
    }

    public function getPermissionsFor(object $user)
    {
        return $user->getCachedPermissions();
    }
}