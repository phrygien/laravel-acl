<?php

namespace MecenePhrygien\LaravelAcl\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YourName\LaravelAcl\Models\Role createRole(string $name, string $slug, string $description = '')
 * @method static \YourName\LaravelAcl\Models\Permission createPermission(string $name, string $slug, string $description = '')
 * @method static void assignPermissionToRole(string $roleSlug, string|array $permissionSlugs)
 */
class Acl extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'acl';
    }
}