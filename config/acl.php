<?php

return [
    'user_model' => App\Models\User::class,

    'models' => [
        'role'       => MecenePhrygien\LaravelAcl\Models\Role::class,
        'permission' => MecenePhrygien\LaravelAcl\Models\Permission::class,
    ],

    'tables' => [
        'roles'           => 'roles',
        'permissions'     => 'permissions',
        'role_user'       => 'role_user',
        'permission_role' => 'permission_role',
    ],

    'cache' => [
        'enabled'  => true,
        'duration' => 3600,
        'key'      => 'acl.user.{id}',
    ],

    'redirects' => [
        'login'     => '/login',
        'forbidden' => '/403',
    ],
];