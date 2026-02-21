<?php

namespace MecenePhrygien\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function getTable(): string
    {
        return config('acl.tables.roles', 'roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(
            config('acl.models.permission'),
            config('acl.tables.permission_role')
        );
    }

    public function users()
    {
        return $this->belongsToMany(
            config('acl.user_model'),
            config('acl.tables.role_user')
        );
    }

    public function givePermission(string|Permission $permission): static
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }
        $this->permissions()->syncWithoutDetaching([$permission->id]);
        return $this;
    }

    public function revokePermission(string|Permission $permission): static
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)->firstOrFail();
        }
        $this->permissions()->detach($permission->id);
        return $this;
    }

    public function syncPermissions(array $slugs): static
    {
        $ids = Permission::whereIn('slug', $slugs)->pluck('id');
        $this->permissions()->sync($ids);
        return $this;
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }
}