<?php

use Illuminate\Support\Facades\Route;
use MecenePhrygien\LaravelAcl\Http\Controllers\AclController;
use MecenePhrygien\LaravelAcl\Http\Controllers\RoleController;
use MecenePhrygien\LaravelAcl\Http\Controllers\PermissionController;
use MecenePhrygien\LaravelAcl\Http\Controllers\UserRoleController;

$prefix     = config('acl.ui.prefix', 'acl');
$middleware = config('acl.ui.middleware', ['web', 'auth']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->name('acl.')
    ->group(function () {

        // Dashboard
        Route::get('/', [AclController::class, 'dashboard'])->name('dashboard');

        // Rôles
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
        Route::post('/roles/{role}/permissions', [RoleController::class, 'syncPermissions'])->name('roles.permissions.sync');

        // Permissions
        Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

        // Utilisateurs & affectations
        Route::get('/users', [UserRoleController::class, 'index'])->name('users.index');
        Route::post('/users/assign', [UserRoleController::class, 'assign'])->name('users.assign');
        Route::post('/users/revoke', [UserRoleController::class, 'revoke'])->name('users.revoke');
        Route::post('/users/sync', [UserRoleController::class, 'sync'])->name('users.sync');

        // routes/web.php — ajouter :
        Route::get('/affectation', [UserRoleController::class, 'affectation'])->name('users.affectation');

        // ── Audit Log ─────────────────────────────────────────
        Route::get('/audit',         [AuditController::class, 'index'])->name('audit.index');
        Route::get('/audit/export',  [AuditController::class, 'export'])->name('audit.export');
        Route::get('/audit/stats',   [AuditController::class, 'stats'])->name('audit.stats');
        Route::get('/audit/{auditLog}',    [AuditController::class, 'show'])->name('audit.show');
        Route::delete('/audit/{auditLog}', [AuditController::class, 'destroy'])->name('audit.destroy');
        Route::post('/audit/purge',        [AuditController::class, 'purge'])->name('audit.purge');
    });