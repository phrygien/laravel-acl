<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ACL Manager · @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0a0a0f;
            --surface:  #111118;
            --surface2: #18181f;
            --border:   #2a2a38;
            --accent:   #6366f1;
            --accent2:  #a78bfa;
            --green:    #22d3a5;
            --red:      #f43f5e;
            --yellow:   #fbbf24;
            --text:     #e2e8f0;
            --muted:    #64748b;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Syne', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; }
        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ── Sidebar ──────────────────────────────────────────────── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 50;
            transition: transform 0.25s;
        }
        .sidebar-logo {
            padding: 22px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .sidebar-logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--accent), var(--accent2));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .sidebar-logo-text  { font-size: 17px; font-weight: 800; letter-spacing: -0.5px; }
        .sidebar-logo-sub   {
            font-size: 10px; color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 2px; text-transform: uppercase;
        }

        .nav-scroll { flex: 1; overflow-y: auto; padding: 8px 0 16px; }
        .nav-section {
            padding: 14px 20px 6px;
            font-size: 10px; color: var(--muted);
            letter-spacing: 2px; text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
        }
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 14px;
            margin: 2px 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px; font-weight: 500;
            color: var(--muted);
            transition: background 0.15s, color 0.15s;
        }
        .nav-item:hover  { background: var(--surface2); color: var(--text); }
        .nav-item.active { background: rgba(99,102,241,0.15); color: var(--accent2); }
        .nav-item .nav-icon { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
        .nav-item .nav-dot  {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 6px var(--green);
            margin-left: auto; flex-shrink: 0;
        }
        .nav-item .nav-badge {
            margin-left: auto;
            background: rgba(99,102,241,0.2);
            color: var(--accent2);
            font-size: 10px; font-weight: 700;
            padding: 1px 7px; border-radius: 99px;
            font-family: 'JetBrains Mono', monospace;
        }

        .sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--border);
            flex-shrink: 0;
        }
        .sidebar-user {
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-user-info  { overflow: hidden; }
        .sidebar-user-name  { font-size: 13px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .sidebar-user-email { font-size: 11px; color: var(--muted); font-family: 'JetBrains Mono', monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* ── Main ─────────────────────────────────────────────────── */
        .main { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Topbar ───────────────────────────────────────────────── */
        .topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
            display: flex; align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            position: sticky; top: 0; z-index: 40;
            flex-shrink: 0;
        }
        .topbar-left  { display: flex; align-items: center; gap: 12px; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .page-title   { font-size: 19px; font-weight: 800; letter-spacing: -0.5px; }

        /* ── Content ──────────────────────────────────────────────── */
        .content { padding: 28px 32px; flex: 1; }

        /* ── Breadcrumb ───────────────────────────────────────────── */
        .breadcrumb {
            display: flex; align-items: center; gap: 6px;
            margin-bottom: 20px;
            font-size: 12px; color: var(--muted);
            font-family: 'JetBrains Mono', monospace;
        }
        .breadcrumb a:hover { color: var(--accent2); }
        .breadcrumb-sep { opacity: 0.4; }
        .breadcrumb-current { color: var(--text); font-weight: 600; }

        /* ── Cards ────────────────────────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-title { font-size: 15px; font-weight: 700; }

        /* ── Buttons ──────────────────────────────────────────────── */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; border: none;
            font-family: 'Syne', sans-serif;
            transition: background 0.15s, transform 0.1s, opacity 0.15s;
            white-space: nowrap;
        }
        .btn:disabled { opacity: 0.45; cursor: not-allowed; }
        .btn-primary  { background: var(--accent); color: white; }
        .btn-primary:hover:not(:disabled)  { background: #5153cc; transform: translateY(-1px); }
        .btn-ghost    { background: transparent; color: var(--muted); border: 1px solid var(--border); }
        .btn-ghost:hover:not(:disabled)    { border-color: var(--accent); color: var(--accent2); }
        .btn-danger   { background: rgba(244,63,94,0.1); color: var(--red); border: 1px solid rgba(244,63,94,0.2); }
        .btn-danger:hover:not(:disabled)   { background: rgba(244,63,94,0.2); }
        .btn-success  { background: rgba(34,211,165,0.1); color: var(--green); border: 1px solid rgba(34,211,165,0.2); }
        .btn-success:hover:not(:disabled)  { background: rgba(34,211,165,0.2); }
        .btn-sm  { padding: 6px 12px; font-size: 12px; }
        .btn-xs  { padding: 3px 8px;  font-size: 11px; border-radius: 5px; }

        /* ── Badges ───────────────────────────────────────────────── */
        .badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 9px;
            border-radius: 999px;
            font-size: 11px; font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            white-space: nowrap;
        }
        .badge-green  { background: rgba(34,211,165,0.12);  color: var(--green);   border: 1px solid rgba(34,211,165,0.22); }
        .badge-purple { background: rgba(99,102,241,0.12);  color: var(--accent2); border: 1px solid rgba(99,102,241,0.22); }
        .badge-red    { background: rgba(244,63,94,0.12);   color: var(--red);     border: 1px solid rgba(244,63,94,0.22); }
        .badge-yellow { background: rgba(251,191,36,0.12);  color: var(--yellow);  border: 1px solid rgba(251,191,36,0.22); }

        /* ── Chip ─────────────────────────────────────────────────── */
        .chip {
            display: inline-flex; align-items: center;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px; font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            background: rgba(99,102,241,0.12);
            color: var(--accent2);
            border: 1px solid rgba(99,102,241,0.2);
            white-space: nowrap;
        }

        /* ── Inputs ───────────────────────────────────────────────── */
        .input {
            background: var(--surface2);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 14px;
            font-family: 'Syne', sans-serif;
            width: 100%;
            transition: border-color 0.15s, box-shadow 0.15s;
            outline: none;
        }
        .input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(99,102,241,0.12); }
        .input::placeholder { color: var(--muted); }
        .input:disabled { opacity: 0.5; cursor: not-allowed; }
        select.input option { background: #111118; }
        textarea.input { resize: vertical; }
        .label {
            display: block;
            font-size: 12px; font-weight: 600; color: var(--muted);
            margin-bottom: 6px; letter-spacing: 0.4px;
        }
        .input-group { display: flex; flex-direction: column; gap: 0; }
        .input-group .input:not(:last-child) { border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-bottom: none; }
        .input-group .input:not(:first-child) { border-top-left-radius: 0; border-top-right-radius: 0; }

        /* Search */
        .search-wrap  { position: relative; }
        .search-icon  { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
        .search-input { padding-left: 34px !important; }

        /* ── Table ────────────────────────────────────────────────── */
        .table { width: 100%; border-collapse: collapse; }
        .table th {
            padding: 11px 16px; text-align: left;
            font-size: 11px; font-weight: 600; color: var(--muted);
            letter-spacing: 1px; text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
            background: var(--surface2);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }
        .table td {
            padding: 13px 16px; font-size: 14px;
            border-bottom: 1px solid rgba(42,42,56,0.5);
            vertical-align: middle;
        }
        .table tbody tr:last-child td { border-bottom: none; }
        .table tbody tr:hover td { background: rgba(255,255,255,0.018); }

        /* ── Avatar ───────────────────────────────────────────────── */
        .avatar {
            width: 32px; height: 32px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            flex-shrink: 0;
        }
        .avatar-lg { width: 40px; height: 40px; font-size: 15px; }

        /* Role colors */
        .role-admin    { background: rgba(244,63,94,0.2);  color: #f43f5e; }
        .role-vendor   { background: rgba(251,191,36,0.2); color: #fbbf24; }
        .role-customer { background: rgba(34,211,165,0.2); color: #22d3a5; }
        .role-editor   { background: rgba(99,102,241,0.2); color: #a78bfa; }

        /* ── Toggle ───────────────────────────────────────────────── */
        .toggle {
            width: 38px; height: 21px;
            background: var(--border);
            border-radius: 999px;
            position: relative; cursor: pointer;
            transition: background 0.2s;
            flex-shrink: 0;
        }
        .toggle.on { background: var(--accent); }
        .toggle::after {
            content: '';
            position: absolute;
            width: 15px; height: 15px;
            background: white; border-radius: 50%;
            top: 3px; left: 3px;
            transition: transform 0.2s;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }
        .toggle.on::after { transform: translateX(17px); }

        /* ── Modal ────────────────────────────────────────────────── */
        .modal-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.65);
            backdrop-filter: blur(5px);
            z-index: 100;
            display: none; align-items: center; justify-content: center;
            padding: 16px;
        }
        .modal-overlay.open { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            width: 520px; max-width: 100%;
            max-height: 88vh;
            display: flex; flex-direction: column;
            animation: modalIn 0.2s ease;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5);
        }
        .modal-lg { width: 680px; }
        .modal-sm { width: 380px; }
        @keyframes modalIn {
            from { opacity: 0; transform: translateY(14px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        .modal-header {
            padding: 22px 24px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0;
        }
        .modal-title    { font-size: 16px; font-weight: 800; }
        .modal-subtitle { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .modal-body {
            padding: 22px 24px;
            display: flex; flex-direction: column; gap: 16px;
            overflow-y: auto; flex: 1;
        }
        .modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            display: flex; justify-content: flex-end; gap: 8px;
            flex-shrink: 0;
        }

        /* ── Perm groups ──────────────────────────────────────────── */
        .perm-group { background: var(--surface2); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .perm-group-header {
            padding: 10px 14px;
            background: rgba(99,102,241,0.07);
            border-bottom: 1px solid var(--border);
            font-size: 11px; font-weight: 700;
            color: var(--accent2);
            letter-spacing: 1px; text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
            display: flex; align-items: center; gap: 8px;
        }
        .perm-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px;
            border-bottom: 1px solid rgba(42,42,56,0.4);
            font-size: 13px;
            gap: 10px;
        }
        .perm-item:last-child { border-bottom: none; }
        .perm-slug { font-family: 'JetBrains Mono', monospace; font-size: 12px; color: var(--green); }

        /* ── Stat cards ───────────────────────────────────────────── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
        }
        .stat-card.s-purple::before { background: linear-gradient(90deg, var(--accent), var(--accent2)); }
        .stat-card.s-green::before  { background: var(--green); }
        .stat-card.s-red::before    { background: var(--red); }
        .stat-card.s-yellow::before { background: var(--yellow); }
        .stat-num   { font-size: 34px; font-weight: 800; letter-spacing: -2px; line-height: 1; }
        .stat-label { font-size: 12px; color: var(--muted); margin-top: 6px; }
        .stat-icon  { font-size: 22px; margin-bottom: 10px; }

        /* ── Toast ────────────────────────────────────────────────── */
        .toast {
            position: fixed; bottom: 24px; right: 24px;
            background: var(--surface);
            border: 1px solid var(--green);
            color: var(--green);
            border-radius: 10px;
            padding: 13px 20px;
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 600;
            z-index: 999;
            transform: translateY(80px); opacity: 0;
            transition: transform 0.28s, opacity 0.28s;
            box-shadow: 0 4px 24px rgba(34,211,165,0.15);
            pointer-events: none;
            max-width: 360px;
        }
        .toast.show     { transform: translateY(0); opacity: 1; }
        .toast.is-error { border-color: var(--red); color: var(--red); box-shadow: 0 4px 24px rgba(244,63,94,0.15); }

        /* ── Alert banners ────────────────────────────────────────── */
        .alert {
            padding: 12px 16px; border-radius: 8px; font-size: 13px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success { background: rgba(34,211,165,0.08);  border: 1px solid rgba(34,211,165,0.22); color: var(--green); }
        .alert-error   { background: rgba(244,63,94,0.08);   border: 1px solid rgba(244,63,94,0.22);  color: var(--red); }
        .alert-warning { background: rgba(251,191,36,0.08);  border: 1px solid rgba(251,191,36,0.22); color: var(--yellow); }
        .alert-info    { background: rgba(99,102,241,0.08);  border: 1px solid rgba(99,102,241,0.22); color: var(--accent2); }

        /* ── Divider ──────────────────────────────────────────────── */
        .divider { height: 1px; background: var(--border); margin: 4px 0; }

        /* ── Empty state ──────────────────────────────────────────── */
        .empty-state {
            padding: 60px 20px; text-align: center;
            color: var(--muted);
        }
        .empty-state-icon  { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }
        .empty-state-title { font-size: 16px; font-weight: 700; margin-bottom: 6px; }
        .empty-state-text  { font-size: 13px; }

        /* ── Spinner ──────────────────────────────────────────────── */
        .spinner {
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.2);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            display: inline-block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Pagination ───────────────────────────────────────────── */
        .pagination { display: flex; gap: 5px; }
        .pagination a, .pagination span {
            padding: 6px 11px; border-radius: 7px; font-size: 12px; font-weight: 600;
            border: 1px solid var(--border); color: var(--muted);
            transition: all 0.15s; cursor: pointer;
        }
        .pagination a:hover   { border-color: var(--accent); color: var(--accent2); }
        .pagination .current  { background: var(--accent); color: white; border-color: var(--accent); }
        .pagination .disabled { opacity: 0.35; cursor: not-allowed; }

        /* ── Responsive mobile ────────────────────────────────────── */
        .menu-toggle {
            display: none;
            background: none; border: none; color: var(--text);
            font-size: 22px; cursor: pointer; padding: 4px;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .menu-toggle { display: block; }
            .sidebar-backdrop {
                display: none; position: fixed; inset: 0;
                background: rgba(0,0,0,0.5); z-index: 49;
            }
            .sidebar-backdrop.open { display: block; }
            .topbar { padding: 0 16px; }
            .content { padding: 20px 16px; }
        }

        /* ── Scrollbar ────────────────────────────────────────────── */
        ::-webkit-scrollbar       { width: 4px; height: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }
    </style>
    @stack('styles')
</head>

<body>

{{-- Backdrop mobile --}}
<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="closeSidebar()"></div>

{{-- ══ SIDEBAR ══════════════════════════════════════════════════ --}}
<aside class="sidebar" id="sidebar">

    {{-- Logo --}}
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">🔐</div>
        <div>
            <div class="sidebar-logo-text">ACL Manager</div>
            <div class="sidebar-logo-sub">{{ config('app.name') }}</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="nav-scroll">

        <div class="nav-section">Navigation</div>

        <a href="{{ route('acl.dashboard') }}"
           class="nav-item {{ request()->routeIs('acl.dashboard') ? 'active' : '' }}">
            <span class="nav-icon">📊</span>
            Dashboard
            @if(request()->routeIs('acl.dashboard'))
                <span class="nav-dot"></span>
            @endif
        </a>

        <a href="{{ route('acl.roles.index') }}"
           class="nav-item {{ request()->routeIs('acl.roles.*') ? 'active' : '' }}">
            <span class="nav-icon">🎭</span>
            Rôles
            @php $roleCount = cache()->remember('acl.roles.count', 60, fn() => \YourName\LaravelAcl\Models\Role::count()); @endphp
            <span class="nav-badge">{{ $roleCount }}</span>
        </a>

        <a href="{{ route('acl.permissions.index') }}"
           class="nav-item {{ request()->routeIs('acl.permissions.*') ? 'active' : '' }}">
            <span class="nav-icon">🔑</span>
            Permissions
            @php $permCount = cache()->remember('acl.perms.count', 60, fn() => \YourName\LaravelAcl\Models\Permission::count()); @endphp
            <span class="nav-badge">{{ $permCount }}</span>
        </a>

        <a href="{{ route('acl.users.index') }}"
           class="nav-item {{ request()->routeIs('acl.users.index') ? 'active' : '' }}">
            <span class="nav-icon">👥</span>
            Utilisateurs
        </a>

        <a href="{{ route('acl.users.affectation') }}"
           class="nav-item {{ request()->routeIs('acl.users.affectation') ? 'active' : '' }}">
            <span class="nav-icon">🔗</span>
            Affectations
        </a>

        <div class="nav-section" style="margin-top:8px;">Système</div>

        <a href="{{ route('acl.audit.index') }}"
           class="nav-item {{ request()->routeIs('acl.audit.*') ? 'active' : '' }}">
            <span class="nav-icon">📋</span>
            Audit Log
        </a>

        {{-- Slot pour liens supplémentaires --}}
        @stack('nav-links')

    </nav>

    {{-- Footer utilisateur --}}
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar avatar-lg"
                 style="background: linear-gradient(135deg, #6366f1, #a78bfa);">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-email">{{ auth()->user()->email }}</div>
            </div>
            {{-- Logout --}}
            @if(Route::has('logout'))
                <form method="POST" action="{{ route('logout') }}" style="margin-left:auto;">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-xs"
                            title="Se déconnecter" style="padding:5px 7px;">
                        ↪
                    </button>
                </form>
            @endif
        </div>
    </div>

</aside>

{{-- ══ MAIN ═══════════════════════════════════════════════════════ --}}
<div class="main">

    {{-- ── Topbar ─────────────────────────────────────────────── --}}
    <header class="topbar">
        <div class="topbar-left">
            {{-- Mobile menu toggle --}}
            <button class="menu-toggle" onclick="toggleSidebar()">☰</button>

            <div class="page-title">@yield('page-title', 'Dashboard')</div>

            {{-- Status badge --}}
            @yield('page-badge')
        </div>

        <div class="topbar-right">
            @yield('topbar-actions')
        </div>
    </header>

    {{-- ── Content ─────────────────────────────────────────────── --}}
    <main class="content">

        {{-- Breadcrumb --}}
        @hasSection('breadcrumb')
            <nav class="breadcrumb">
                <a href="{{ route('acl.dashboard') }}">ACL</a>
                <span class="breadcrumb-sep">/</span>
                @yield('breadcrumb')
            </nav>
        @endif

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert alert-success" style="margin-bottom:20px;">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error" style="margin-bottom:20px;">
                ❌ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error" style="margin-bottom:20px;">
                <div>
                    @foreach($errors->all() as $error)
                        <div>• {{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Page content --}}
        @yield('content')

    </main>

</div>

{{-- ══ TOAST ═══════════════════════════════════════════════════════ --}}
<div class="toast" id="toast">
    <span id="toast-icon">✅</span>
    <span id="toast-msg"></span>
</div>

{{-- ══ SCRIPTS GLOBAUX ═════════════════════════════════════════════ --}}
<script>
    // ── CSRF & API helper ─────────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;

    async function apiCall(url, method = 'POST', body = {}) {
        const opts = {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept':       'application/json',
            },
        };
        if (!['GET','HEAD'].includes(method.toUpperCase())) {
            opts.body = JSON.stringify(body);
        }
        const res  = await fetch(url, opts);
        const data = await res.json();
        if (!res.ok) throw new Error(data.message || data.error || 'Erreur serveur');
        return data;
    }

    // ── Toast ─────────────────────────────────────────────────────
    let toastTimer;
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        document.getElementById('toast-icon').textContent = type === 'error' ? '❌' : '✅';
        document.getElementById('toast-msg').textContent  = msg;
        toast.classList.toggle('is-error', type === 'error');
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    // ── Modals ────────────────────────────────────────────────────
    function openModal(id) {
        document.getElementById(id)?.classList.add('open');
    }
    function closeModal(id) {
        document.getElementById(id)?.classList.remove('open');
    }
    // Fermer au clic sur l'overlay
    document.addEventListener('click', e => {
        if (e.target.classList.contains('modal-overlay')) {
            e.target.classList.remove('open');
        }
    });
    // Fermer avec Escape
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.open')
                .forEach(m => m.classList.remove('open'));
        }
    });

    // ── Sidebar mobile ────────────────────────────────────────────
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebar-backdrop').classList.toggle('open');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-backdrop').classList.remove('open');
    }

    // ── Confirm helper ────────────────────────────────────────────
    function confirmAction(message, callback) {
        if (window.confirm(message)) callback();
    }

    // ── Loading state sur bouton ──────────────────────────────────
    function setLoading(btn, loading) {
        if (loading) {
            btn.dataset.original = btn.innerHTML;
            btn.innerHTML = '<span class="spinner"></span> Chargement...';
            btn.disabled  = true;
        } else {
            btn.innerHTML = btn.dataset.original || btn.innerHTML;
            btn.disabled  = false;
        }
    }
</script>

@stack('scripts')

</body>
</html>