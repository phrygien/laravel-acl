<?php

namespace MecenePhrygien\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function getTable(): string
    {
        return config('acl.tables.permissions', 'permissions');
    }

    public function roles()
    {
        return $this->belongsToMany(
            config('acl.models.role'),
            config('acl.tables.permission_role')
        );
    }
}