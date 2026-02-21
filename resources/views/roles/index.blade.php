@extends('acl::layouts.app')

@section('title', 'Rôles')
@section('page-title', '🎭 Gestion des rôles')

@section('topbar-actions')
    <button class="btn btn-primary" onclick="openModal('modal-create-role')">
        + Nouveau rôle
    </button>
@endsection

@section('content')
    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
        @foreach($roles as $role)
            <div class="card" style="border-color:rgba(99,102,241,0.3);">
                <div class="card-header">
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="avatar role-{{ $role->slug }}" style="width:38px;height:38px;">
                            {{ strtoupper(substr($role->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="card-title">{{ $role->name }}</div>
                            <div class="mono" style="font-size:11px;color:var(--muted);">slug: {{ $role->slug }}</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:6px;">
                        <button class="btn btn-ghost btn-xs"
                                onclick="openEditRole({{ $role->id }}, '{{ $role->name }}', '{{ $role->slug }}', '{{ $role->description }}')">
                            ✏️
                        </button>
                        <button class="btn btn-ghost btn-xs"
                                onclick="openRolePerms({{ $role->id }}, '{{ $role->name }}', {{ $role->permissions->pluck('id')->toJson() }})">
                            🔑
                        </button>
                        <button class="btn btn-danger btn-xs"
                                onclick="deleteRole({{ $role->id }}, '{{ $role->name }}', this)">
                            🗑
                        </button>
                    </div>
                </div>
                <div style="padding:16px 20px;">
                    <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">
                        {{ $role->description ?? 'Aucune description.' }}
                    </div>
                    <div style="display:flex;flex-wrap:wrap;gap:5px;margin-bottom:12px;">
                        @foreach($role->permissions->take(5) as $perm)
                            <span class="chip">{{ $perm->slug }}</span>
                        @endforeach
                        @if($role->permissions->count() > 5)
                            <span class="chip" style="background:rgba(34,211,165,0.1);color:var(--green);">
            +{{ $role->permissions->count() - 5 }} autres
          </span>
                        @endif
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;">
                        <span class="badge badge-purple">{{ $role->users_count }} utilisateurs</span>
                        <span style="font-size:11px;color:var(--muted);">
          Modifié {{ $role->updated_at->diffForHumans() }}
        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal créer/éditer rôle --}}
    <div class="modal-overlay" id="modal-create-role">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="role-modal-title">🎭 Nouveau rôle</div>
                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-create-role')">✕</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="role-id">
                <div>
                    <label class="label">Nom du rôle</label>
                    <input class="input" id="role-name" placeholder="ex: Moderator">
                </div>
                <div>
                    <label class="label">Slug</label>
                    <input class="input mono" id="role-slug" placeholder="moderator" style="color:var(--green);">
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea class="input" id="role-desc" rows="2" placeholder="Description..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('modal-create-role')">Annuler</button>
                <button class="btn btn-primary" onclick="saveRole()">✅ Sauvegarder</button>
            </div>
        </div>
    </div>

    {{-- Modal permissions --}}
    <div class="modal-overlay" id="modal-role-perms">
        <div class="modal" style="width:580px;">
            <div class="modal-header">
                <div class="modal-title">🔑 Permissions · <span id="modal-role-name"></span></div>
                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-role-perms')">✕</button>
            </div>
            <div id="perms-body" style="padding:20px;display:flex;flex-direction:column;gap:8px;max-height:55vh;overflow-y:auto;">
                @php $grouped = $permissions->groupBy(fn($p) => explode('.', $p->slug)[0]); @endphp
                @foreach($grouped as $module => $perms)
                    <div class="perm-group">
                        <div class="perm-group-header">{{ strtoupper($module) }}</div>
                        @foreach($perms as $perm)
                            <div class="perm-item">
                                <span class="perm-slug">{{ $perm->slug }}</span>
                                <div class="toggle" id="ptog-{{ $perm->id }}"
                                     onclick="this.classList.toggle('on')"
                                     data-id="{{ $perm->id }}"></div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('modal-role-perms')">Annuler</button>
                <button class="btn btn-primary" onclick="saveRolePerms()">💾 Sauvegarder</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentRoleId = null;

        document.getElementById('role-name').addEventListener('input', function() {
            document.getElementById('role-slug').value = this.value.toLowerCase()
                .replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
        });

        function openEditRole(id, name, slug, desc) {
            document.getElementById('role-modal-title').textContent = '✏️ Modifier le rôle';
            document.getElementById('role-id').value = id;
            document.getElementById('role-name').value = name;
            document.getElementById('role-slug').value = slug;
            document.getElementById('role-desc').value = desc;
            openModal('modal-create-role');
        }

        async function saveRole() {
            const id   = document.getElementById('role-id').value;
            const body = {
                name:        document.getElementById('role-name').value,
                slug:        document.getElementById('role-slug').value,
                description: document.getElementById('role-desc').value,
            };
            try {
                const url    = id ? `/acl/roles/${id}` : '/acl/roles';
                const method = id ? 'PUT' : 'POST';
                const data   = await apiCall(url, method, body);
                closeModal('modal-create-role');
                showToast(data.message);
                setTimeout(() => location.reload(), 800);
            } catch (e) { showToast(e.message, 'error'); }
        }

        async function deleteRole(id, name, btn) {
            if (! confirm(`Supprimer le rôle "${name}" ?`)) return;
            try {
                const data = await apiCall(`/acl/roles/${id}`, 'DELETE');
                btn.closest('.card').remove();
                showToast(data.message);
            } catch (e) { showToast(e.message, 'error'); }
        }

        function openRolePerms(id, name, activeIds) {
            currentRoleId = id;
            document.getElementById('modal-role-name').textContent = name;
            // reset tous les toggles
            document.querySelectorAll('[id^="ptog-"]').forEach(t => t.classList.remove('on'));
            // activer ceux du rôle
            activeIds.forEach(pid => {
                const tog = document.getElementById('ptog-' + pid);
                if (tog) tog.classList.add('on');
            });
            openModal('modal-role-perms');
        }

        async function saveRolePerms() {
            const permissions = [...document.querySelectorAll('[id^="ptog-"].on')]
                .map(t => parseInt(t.dataset.id));
            try {
                const data = await apiCall(`/acl/roles/${currentRoleId}/permissions`, 'POST', { permissions });
                closeModal('modal-role-perms');
                showToast(data.message);
                setTimeout(() => location.reload(), 800);
            } catch (e) { showToast(e.message, 'error'); }
        }
    </script>
@endsection