<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Routing\Controller;
use MecenePhrygien\LaravelAcl\Models\Role;
use MecenePhrygien\LaravelAcl\Models\Permission;

class AclController extends Controller
{
    public function __construct()
    {
        // Protéger toutes les routes du panel
        $this->middleware(config('acl.ui.middleware', ['web', 'auth']));
    }

    public function dashboard()
    {
        $stats = [
            'roles'       => Role::count(),
            'permissions' => Permission::count(),
            'users'       => config('acl.user_model')::count(),
        ];

        $rolesWithCount = Role::withCount('users')->get();

        $recentActivity = \MecenePhrygien\LaravelAcl\Models\AuditLog::latest()
            ->take(5)
            ->get();

        return view('acl::dashboard', compact('stats', 'rolesWithCount', 'recentActivity'));
    }
}