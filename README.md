# Laravel ACL

Gestion des rôles et permissions pour Laravel 11/12.

## Installation
```bash
composer require MecenePhrygien/laravel-acl
php artisan vendor:publish --tag=acl-config
php artisan vendor:publish --tag=acl-migrations
php artisan migrate
```

## Configuration

Ajouter le trait au model User :
```php
use MecenePhrygien\LaravelAcl\Traits\HasRolesAndPermissions;
use MecenePhrygien\LaravelAcl\Contracts\HasAcl;

class User extends Authenticatable implements HasAcl
{
    use HasRolesAndPermissions;
}
```

## Utilisation
```php
$user->assignRole('admin');
$user->hasRole('admin');           // true
$user->hasPermission('users.edit'); // true
```

## Middlewares
```php
Route::middleware(['auth', 'role:admin'])->group(...);
Route::middleware(['auth', 'permission:products.edit'])->group(...);
Route::middleware(['auth', 'role_or_permission:admin,orders.view'])->group(...);
```