@extends('acl::layouts.app')

@section('title', 'Affectations')
@section('page-title', '🔗 Affectation des rôles')

@section('content')

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

        {{-- ── Affecter un rôle ───────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">🔗 Affecter un rôle</div>
                <span class="badge badge-green mono">assignRole()</span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">

                <div>
                    <label class="label">Utilisateur</label>
                    <select class="input" id="aff-user" onchange="loadUserRoles('aff')">
                        <option value="">— Sélectionner —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-roles="{{ $user->roles->pluck('slug')->toJson() }}">
                                {{ $user->name }} · {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Rôles actuels --}}
                <div>
                    <label class="label">Rôles actuels</label>
                    <div id="aff-current-roles"
                         style="display:flex;flex-wrap:wrap;gap:5px;min-height:34px;
                    padding:8px 12px;background:var(--surface2);
                    border:1px solid var(--border);border-radius:8px;">
                        <span style="font-size:12px;color:var(--muted);">Sélectionnez un utilisateur</span>
                    </div>
                </div>

                <div>
                    <label class="label">Rôle à affecter</label>
                    <select class="input" id="aff-role" onchange="previewRolePerms()">
                        <option value="">— Sélectionner —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->slug }}"
                                    data-perms="{{ $role->permissions->pluck('slug')->toJson() }}">
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Preview permissions --}}
                <div style="background:var(--surface2);border:1px solid var(--border);
                  border-radius:8px;padding:14px;">
                    <div style="font-size:11px;font-weight:600;color:var(--muted);
                    margin-bottom:8px;font-family:'JetBrains Mono',monospace;">
                        PERMISSIONS DU RÔLE
                    </div>
                    <div id="aff-perm-preview" style="display:flex;flex-wrap:wrap;gap:5px;">
          <span style="font-size:12px;color:var(--muted);">
            Sélectionnez un rôle pour voir ses permissions
          </span>
                    </div>
                </div>

                <button class="btn btn-primary" style="width:100%;justify-content:center;"
                        onclick="affecterRole()">
                    ✅ Affecter le rôle
                </button>
            </div>
        </div>

        {{-- ── Révoquer un rôle ───────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">🚫 Révoquer un rôle</div>
                <span class="badge badge-red mono">removeRole()</span>
            </div>
            <div style="padding:24px;display:flex;flex-direction:column;gap:16px;">

                <div>
                    <label class="label">Utilisateur</label>
                    <select class="input" id="rev-user" onchange="loadUserRoles('rev')">
                        <option value="">— Sélectionner —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                    data-roles="{{ $user->roles->pluck('slug')->toJson() }}">
                                {{ $user->name }} · {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="label">Rôles actuels</label>
                    <div id="rev-current-roles"
                         style="display:flex;flex-wrap:wrap;gap:5px;min-height:34px;
                    padding:8px 12px;background:var(--surface2);
                    border:1px solid var(--border);border-radius:8px;">
                        <span style="font-size:12px;color:var(--muted);">Sélectionnez un utilisateur</span>
                    </div>
                </div>

                <div>
                    <label class="label">Rôle à révoquer</label>
                    <select class="input" id="rev-role">
                        <option value="">— Sélectionner —</option>
                    </select>
                </div>

                <div style="background:rgba(244,63,94,0.05);border:1px solid rgba(244,63,94,0.2);
                  border-radius:8px;padding:12px;font-size:12px;color:var(--red);">
                    ⚠️ La révocation est immédiate. L'utilisateur perdra
                    toutes les permissions liées à ce rôle.
                </div>

                <button class="btn btn-danger" style="width:100%;justify-content:center;"
                        onclick="revoquerRole()">
                    🚫 Révoquer le rôle
                </button>
            </div>
        </div>

        {{-- ── Sync en masse ───────────────────────────────────────── --}}
        <div class="card" style="grid-column:1/-1;">
            <div class="card-header">
                <div class="card-title">⚡ Synchronisation en masse</div>
                <span class="badge badge-yellow mono">syncRoles()</span>
            </div>
            <div style="padding:24px;">
                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:24px;align-items:start;">

                    {{-- Colonne gauche : user + rôles actuels --}}
                    <div style="display:flex;flex-direction:column;gap:12px;">
                        <div>
                            <label class="label">Utilisateur cible</label>
                            <select class="input" id="sync-user" onchange="loadSyncUser()">
                                <option value="">— Sélectionner —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}"
                                            data-roles="{{ $user->roles->pluck('slug')->toJson() }}">
                                        {{ $user->name }} · {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label">Rôles actuels</label>
                            <div id="sync-current-roles"
                                 style="display:flex;flex-wrap:wrap;gap:5px;min-height:34px;
                        padding:8px 12px;background:var(--surface2);
                        border:1px solid var(--border);border-radius:8px;">
                                <span style="font-size:12px;color:var(--muted);">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Flèche centrale --}}
                    <div style="display:flex;flex-direction:column;align-items:center;
                    padding-top:28px;gap:6px;">
                        <div style="font-size:24px;">→</div>
                        <div style="font-size:10px;color:var(--muted);text-align:center;
                      font-family:'JetBrains Mono',monospace;">REMPLACE</div>
                    </div>

                    {{-- Colonne droite : nouveaux rôles --}}
                    <div>
                        <label class="label" style="margin-bottom:12px;">Nouveaux rôles (remplace tout)</label>
                        <div style="display:flex;flex-direction:column;gap:8px;">
                            @foreach($roles as $role)
                                @php
                                    $colors = [
                                      'admin'    => ['rgba(244,63,94,0.08)','rgba(244,63,94,0.25)','var(--red)'],
                                      'vendor'   => ['rgba(251,191,36,0.08)','rgba(251,191,36,0.25)','var(--yellow)'],
                                      'editor'   => ['rgba(99,102,241,0.08)','rgba(99,102,241,0.25)','var(--accent2)'],
                                      'customer' => ['rgba(34,211,165,0.08)','rgba(34,211,165,0.25)','var(--green)'],
                                    ];
                                    [$bg,$border,$color] = $colors[$role->slug] ?? ['rgba(99,102,241,0.08)','rgba(99,102,241,0.25)','var(--accent2)'];
                                @endphp
                                <label style="display:flex;align-items:center;justify-content:space-between;
                          cursor:pointer;padding:10px 14px;border-radius:8px;
                          border:1px solid {{ $border }};background:{{ $bg }};
                          transition:opacity 0.2s;">
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <input type="checkbox"
                                               id="sync-role-{{ $role->slug }}"
                                               value="{{ $role->slug }}"
                                               class="sync-role-cb"
                                               style="accent-color:var(--accent);width:15px;height:15px;">
                                        <span style="font-weight:600;font-size:13px;color:{{ $color }};">
                  {{ $role->name }}
                </span>
                                    </div>
                                    <span style="font-size:11px;color:var(--muted);">
                {{ $role->permissions->count() }} permissions
              </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div style="margin-top:20px;padding:12px 16px;background:rgba(251,191,36,0.06);
                  border:1px solid rgba(251,191,36,0.2);border-radius:8px;
                  font-size:12px;color:var(--yellow);">
                    ⚠️ <strong>syncRoles()</strong> remplace <strong>TOUS</strong> les rôles existants.
                    Les rôles non cochés seront immédiatement révoqués.
                </div>

                <div style="margin-top:16px;display:flex;gap:10px;">
                    <button class="btn btn-primary" onclick="syncRoles()">⚡ Synchroniser</button>
                    <button class="btn btn-ghost" onclick="resetSync()">↺ Réinitialiser</button>
                </div>
            </div>
        </div>

        {{-- ── Aperçu utilisateur ──────────────────────────────────── --}}
        <div class="card" style="grid-column:1/-1;" id="user-preview-card" style="display:none;">
            <div class="card-header">
                <div class="card-title">👁 Aperçu des permissions effectives</div>
                <span style="font-size:12px;color:var(--muted);">
        Union de toutes les permissions via les rôles
      </span>
            </div>
            <div style="padding:20px;">
                <div style="display:flex;gap:12px;margin-bottom:16px;align-items:center;">
                    <select class="input" style="width:280px;" id="preview-user"
                            onchange="loadEffectivePerms()">
                        <option value="">— Sélectionner un utilisateur —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->name }} · {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    <span id="preview-roles-badges" style="display:flex;gap:5px;"></span>
                </div>
                <div id="preview-perms"
                     style="display:flex;flex-wrap:wrap;gap:6px;min-height:40px;">
        <span style="font-size:12px;color:var(--muted);">
          Sélectionnez un utilisateur pour voir ses permissions effectives
        </span>
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        // ─── Données depuis Blade ──────────────────────────────────────
        const ROLE_PERMS = {
            @foreach($roles as $role)
            '{{ $role->slug }}': @json($role->permissions->pluck('slug')),
            @endforeach
        };

        const BADGE_COLORS = {
            admin:    'badge-red',
            vendor:   'badge-yellow',
            editor:   'badge-purple',
            customer: 'badge-green',
        };

        // ─── Helpers ──────────────────────────────────────────────────
        function getBadge(slug) {
            const cls = BADGE_COLORS[slug] ?? 'badge-purple';
            return `<span class="badge ${cls}">${slug}</span>`;
        }

        function getUserRoles(selectId) {
            const sel = document.getElementById(selectId);
            const opt = sel.options[sel.selectedIndex];
            if (!opt?.value) return [];
            try { return JSON.parse(opt.dataset.roles || '[]'); } catch { return []; }
        }

        function renderCurrentRoles(containerId, roles) {
            const el = document.getElementById(containerId);
            el.innerHTML = roles.length
                ? roles.map(getBadge).join('')
                : '<span style="font-size:12px;color:var(--muted);">Aucun rôle assigné</span>';
        }

        // ─── Affecter ─────────────────────────────────────────────────
        function loadUserRoles(prefix) {
            const roles = getUserRoles(prefix + '-user');
            renderCurrentRoles(prefix + '-current-roles', roles);

            if (prefix === 'rev') {
                const sel = document.getElementById('rev-role');
                sel.innerHTML = '<option value="">— Sélectionner —</option>'
                    + roles.map(r => `<option value="${r}">${r}</option>`).join('');
            }
        }

        function previewRolePerms() {
            const sel  = document.getElementById('aff-role');
            const slug = sel.value;
            const el   = document.getElementById('aff-perm-preview');

            if (!slug) {
                el.innerHTML = '<span style="font-size:12px;color:var(--muted);">Sélectionnez un rôle</span>';
                return;
            }
            const perms = ROLE_PERMS[slug] || [];
            el.innerHTML = perms.length
                ? perms.map(p => `<span class="chip">${p}</span>`).join('')
                : '<span style="font-size:12px;color:var(--muted);">Aucune permission</span>';
        }

        async function affecterRole() {
            const userId = document.getElementById('aff-user').value;
            const role   = document.getElementById('aff-role').value;
            if (!userId || !role) {
                showToast('Sélectionnez un utilisateur et un rôle', 'error'); return;
            }
            try {
                const data = await apiCall('{{ route("acl.users.assign") }}', 'POST',
                    { user_id: userId, role });
                showToast(data.message);
                loadUserRoles('aff');
            } catch (e) { showToast(e.message, 'error'); }
        }

        // ─── Révoquer ─────────────────────────────────────────────────
        async function revoquerRole() {
            const userId = document.getElementById('rev-user').value;
            const role   = document.getElementById('rev-role').value;
            if (!userId || !role) {
                showToast('Sélectionnez un utilisateur et un rôle', 'error'); return;
            }
            try {
                const data = await apiCall('{{ route("acl.users.revoke") }}', 'POST',
                    { user_id: userId, role });
                showToast(data.message);
                loadUserRoles('rev');
            } catch (e) { showToast(e.message, 'error'); }
        }

        // ─── Sync ─────────────────────────────────────────────────────
        function loadSyncUser() {
            const roles = getUserRoles('sync-user');
            renderCurrentRoles('sync-current-roles', roles);
            // Pré-cocher les rôles actuels
            document.querySelectorAll('.sync-role-cb').forEach(cb => {
                cb.checked = roles.includes(cb.value);
            });
        }

        async function syncRoles() {
            const userId = document.getElementById('sync-user').value;
            if (!userId) { showToast('Sélectionnez un utilisateur', 'error'); return; }
            const roles = [...document.querySelectorAll('.sync-role-cb:checked')].map(c => c.value);
            try {
                const data = await apiCall('{{ route("acl.users.sync") }}', 'POST',
                    { user_id: userId, roles });
                showToast(data.message);
                loadSyncUser();
            } catch (e) { showToast(e.message, 'error'); }
        }

        function resetSync() {
            document.getElementById('sync-user').value = '';
            document.querySelectorAll('.sync-role-cb').forEach(cb => cb.checked = false);
            renderCurrentRoles('sync-current-roles', []);
        }

        // ─── Aperçu permissions effectives ───────────────────────────
        async function loadEffectivePerms() {
            const userId = document.getElementById('preview-user').value;
            const el     = document.getElementById('preview-perms');
            const badges = document.getElementById('preview-roles-badges');

            if (!userId) {
                el.innerHTML = '<span style="font-size:12px;color:var(--muted);">Sélectionnez un utilisateur</span>';
                badges.innerHTML = '';
                return;
            }

            // Trouver les rôles depuis le select
            const sel   = document.getElementById('preview-user');
            const allRoles = @json($users->mapWithKeys(fn($u) => [$u->id => $u->roles->pluck('slug')]));
            const userRoles = allRoles[userId] || [];

            badges.innerHTML = userRoles.map(getBadge).join('');

            // Union de toutes les permissions
            const allPerms = [...new Set(userRoles.flatMap(r => ROLE_PERMS[r] || []))].sort();
            el.innerHTML = allPerms.length
                ? allPerms.map(p => `<span class="chip">${p}</span>`).join('')
                : '<span style="font-size:12px;color:var(--muted);">Aucune permission</span>';

            document.getElementById('user-preview-card').style.display = 'block';
        }

        // Afficher le card preview au chargement
        document.getElementById('user-preview-card').style.display = 'block';
    </script>
@endsection