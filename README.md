# Laravel ACL

[![Latest Version](https://img.shields.io/packagist/v/MecenePhrygien/laravel-acl.svg)](https://packagist.org/packages/MecenePhrygien/laravel-acl)
[![License](https://img.shields.io/packagist/l/MecenePhrygien/laravel-acl.svg)](LICENSE)

Gestion des rôles et permissions pour Laravel 11/12 avec interface
d'administration intégrée.

## Installation
```bash
composer require MecenePhrygien/laravel-acl
php artisan vendor:publish --tag=acl-config
php artisan vendor:publish --tag=acl-migrations
php artisan migrate
```

## Configuration rapide
```php
// app/Models/User.php
use MecenePhrygien\LaravelAcl\Traits\HasRolesAndPermissions;
use MecenePhrygien\LaravelAcl\Contracts\HasAcl;

class User extends Authenticatable implements HasAcl
{
    use HasRolesAndPermissions;
}
```

## Interface d'administration

Accessible sur `/acl` après connexion :

| URL                  | Description          |
|----------------------|----------------------|
| `/acl`               | Dashboard            |
| `/acl/roles`         | Gestion des rôles    |
| `/acl/permissions`   | Gestion permissions  |
| `/acl/users`         | Utilisateurs         |
| `/acl/affectation`   | Affecter des rôles   |
| `/acl/audit`         | Journal d'audit      |

Personnaliser dans `config/acl.php` :
```php
'ui' => [
    'enabled'    => true,
    'prefix'     => 'acl',
    'middleware' => ['web', 'auth', 'role:admin'],
],
```

## Utilisation
```php
$user->assignRole('admin');
$user->removeRole('vendor');
$user->syncRoles(['vendor', 'editor']);

$user->hasRole('admin');                    // true
$user->hasAnyRole(['admin', 'vendor']);     // true
$user->hasPermission('products.edit');      // true
$user->hasAllPermissions(['p1', 'p2']);     // true
```

## Middlewares
```php
Route::middleware('role:admin')->group(...);
Route::middleware('permission:products.edit')->group(...);
Route::middleware('role_or_permission:admin,orders.view')->group(...);
```