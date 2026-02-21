<?php

namespace MecenePhrygien\LaravelAcl\Traits;

use Illuminate\Support\Facades\Cache;
use YourName\LaravelAcl\Models\Role;
use YourName\LaravelAcl\Models\Permission;

trait HasRolesAndPermissions
{
    public function roles()
    {
        return $this->belongsToMany(
            config('acl.models.role'),
            config('acl.tables.role_user')
        );
    }

    public function hasRole(string $slug): bool
    {
        return $this->getCachedRoles()->contains('slug', $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->getCachedRoles()->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function hasAllRoles(array $slugs): bool
    {
        $userSlugs = $this->getCachedRoles()->pluck('slug');
        return collect($slugs)->every(fn($s) => $userSlugs->contains($s));
    }

    public function hasPermission(string $slug): bool
    {
        return $this->getCachedPermissions()->contains('slug', $slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        return $this->getCachedPermissions()->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function hasAllPermissions(array $slugs): bool
    {
        $userPerms = $this->getCachedPermissions()->pluck('slug');
        return collect($slugs)->every(fn($s) => $userPerms->contains($s));
    }

    public function assignRole(string|Role $role): static
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->clearAclCache();
        $this->unsetRelation('roles');
        return $this;
    }

    public function removeRole(string|Role $role): static
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }
        $this->roles()->detach($role->id);
        $this->clearAclCache();
        $this->unsetRelation('roles');
        return $this;
    }

    public function syncRoles(array $slugs): static
    {
        $ids = Role::whereIn('slug', $slugs)->pluck('id');
        $this->roles()->sync($ids);
        $this->clearAclCache();
        $this->unsetRelation('roles');
        return $this;
    }

    public function getCachedRoles()
    {
        if (! config('acl.cache.enabled')) {
            return $this->loadMissing('roles')->roles;
        }

        return Cache::remember(
            $this->aclCacheKey('roles'),
            config('acl.cache.duration'),
            fn() => $this->loadMissing('roles')->roles
        );
    }

    public function getCachedPermissions()
    {
        if (! config('acl.cache.enabled')) {
            return $this->loadMissing('roles.permissions')
                ->roles
                ->flatMap(fn($r) => $r->permissions);
        }

        return Cache::remember(
            $this->aclCacheKey('permissions'),
            config('acl.cache.duration'),
            fn() => $this->loadMissing('roles.permissions')
                ->roles
                ->flatMap(fn($r) => $r->permissions)
        );
    }

    public function clearAclCache(): void
    {
        Cache::forget($this->aclCacheKey('roles'));
        Cache::forget($this->aclCacheKey('permissions'));
    }

    private function aclCacheKey(string $type): string
    {
        return str_replace('{id}', $this->getKey(), config('acl.cache.key')).'.'.$type;
    }
}