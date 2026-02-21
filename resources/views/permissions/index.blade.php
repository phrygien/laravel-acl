@extends('acl::layouts.app')

@section('title', 'Permissions')
@section('page-title', '🔑 Gestion des permissions')

@section('topbar-actions')
    <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input class="input search-input" style="width:200px;" placeholder="Filtrer..."
               oninput="filterPerms(this.value)">
    </div>
    <button class="btn btn-primary" onclick="openModal('modal-create-perm')">
        + Nouvelle permission
    </button>
@endsection

@section('content')

    {{-- Stats modules --}}
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        @foreach($permissions as $module => $perms)
            <button class="btn btn-ghost btn-sm module-filter"
                    data-module="{{ $module }}"
                    onclick="filterModule(this, '{{ $module }}')">
                {{ strtoupper($module) }}
                <span class="badge badge-purple" style="margin-left:4px;">{{ $perms->count() }}</span>
            </button>
        @endforeach
        <button class="btn btn-ghost btn-sm" onclick="filterModule(null, '')">
            Tout afficher
        </button>
        <span style="margin-left:auto;font-size:12px;color:var(--muted);align-self:center;">
    Total : <strong style="color:var(--text);">{{ $permissions->flatten()->count() }}</strong> permissions
  </span>
    </div>

    {{-- Groupes de permissions --}}
    <div style="display:flex;flex-direction:column;gap:12px;" id="perm-container">

        @foreach($permissions as $module => $perms)
            <div class="perm-group module-group" data-module="{{ $module }}">
                <div class="perm-group-header">
                    @php
                        $icons = [
                          'users'       => '👤',
                          'products'    => '📦',
                          'orders'      => '🛒',
                          'roles'       => '🎭',
                          'permissions' => '🔑',
                          'dashboard'   => '📊',
                          'reports'     => '📈',
                        ];
                        $icon = $icons[$module] ?? '⚙️';
                    @endphp
                    {{ $icon }} {{ ucfirst($module) }}
                    <span style="color:var(--muted);font-weight:400;">
        · {{ $perms->count() }} permission{{ $perms->count() > 1 ? 's' : '' }}
      </span>
                    <button class="btn btn-ghost btn-xs" style="margin-left:auto;"
                            onclick="openCreatePermForModule('{{ $module }}')">
                        + Ajouter
                    </button>
                </div>

                @foreach($perms as $perm)
                    <div class="perm-item perm-row" data-slug="{{ $perm->slug }}" data-id="{{ $perm->id }}">
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;">{{ $perm->name }}</div>
                            <div class="perm-slug">{{ $perm->slug }}</div>
                            @if($perm->description)
                                <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                    {{ $perm->description }}
                                </div>
                            @endif
                        </div>
                        <div style="display:flex;gap:5px;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                            @forelse($perm->roles as $role)
                                @php
                                    $roleColors = [
                                      'admin'    => 'badge-red',
                                      'vendor'   => 'badge-yellow',
                                      'editor'   => 'badge-purple',
                                      'customer' => 'badge-green',
                                    ];
                                    $cls = $roleColors[$role->slug] ?? 'badge-purple';
                                @endphp
                                <span class="badge {{ $cls }}">{{ $role->slug }}</span>
                            @empty
                                <span style="font-size:11px;color:var(--muted);">Aucun rôle</span>
                            @endforelse
                            <button class="btn btn-ghost btn-xs"
                                    onclick="openEditPerm({{ $perm->id }}, '{{ $perm->name }}', '{{ $perm->slug }}', '{{ $perm->description }}')">
                                ✏️
                            </button>
                            <button class="btn btn-danger btn-xs"
                                    onclick="deletePerm({{ $perm->id }}, '{{ $perm->slug }}', this)">
                                🗑
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

    </div>

    {{-- Modal créer / éditer permission --}}
    <div class="modal-overlay" id="modal-create-perm">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title" id="perm-modal-title">🔑 Nouvelle permission</div>
                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-create-perm')">✕</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="perm-id">

                <div>
                    <label class="label">Nom</label>
                    <input class="input" id="perm-name" placeholder="ex: Voir les rapports">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label class="label">Module</label>
                        <select class="input" id="perm-module" onchange="updateSlug()">
                            @foreach($permissions->keys() as $mod)
                                <option>{{ $mod }}</option>
                            @endforeach
                            <option value="custom">custom...</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Action</label>
                        <select class="input" id="perm-action" onchange="updateSlug()">
                            <option>view</option>
                            <option>create</option>
                            <option>edit</option>
                            <option>delete</option>
                            <option>manage</option>
                            <option>assign</option>
                            <option>export</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="label">Slug généré</label>
                    <input class="input mono" id="perm-slug-preview"
                           style="color:var(--green);" readonly>
                </div>

                <div>
                    <label class="label">Description</label>
                    <textarea class="input" id="perm-desc" rows="2"
                              placeholder="Description..."></textarea>
                </div>

                <div>
                    <label class="label" style="margin-bottom:10px;">Assigner aux rôles</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($roles as $role)
                            @php
                                $colors = [
                                  'admin'    => ['rgba(244,63,94,0.08)','rgba(244,63,94,0.3)','var(--red)'],
                                  'vendor'   => ['rgba(251,191,36,0.08)','rgba(251,191,36,0.3)','var(--yellow)'],
                                  'editor'   => ['rgba(99,102,241,0.08)','rgba(99,102,241,0.3)','var(--accent2)'],
                                  'customer' => ['rgba(34,211,165,0.08)','rgba(34,211,165,0.3)','var(--green)'],
                                ];
                                [$bg,$border,$color] = $colors[$role->slug] ?? ['rgba(99,102,241,0.08)','rgba(99,102,241,0.3)','var(--accent2)'];
                            @endphp
                            <label style="display:flex;align-items:center;gap:5px;cursor:pointer;
                        font-size:13px;padding:5px 10px;border-radius:6px;
                        border:1px solid {{ $border }};
                        background:{{ $bg }};
                        color:{{ $color }};">
                                <input type="checkbox" name="perm_roles[]"
                                       value="{{ $role->id }}"
                                       style="accent-color:var(--accent);">
                                {{ $role->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('modal-create-perm')">Annuler</button>
                <button class="btn btn-primary" onclick="savePerm()">✅ Sauvegarder</button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // ── Slug auto ─────────────────────────────────────────────────
        function updateSlug() {
            const m = document.getElementById('perm-module').value;
            const a = document.getElementById('perm-action').value;
            document.getElementById('perm-slug-preview').value = `${m}.${a}`;
        }
        updateSlug();

        // ── Filtres ───────────────────────────────────────────────────
        function filterModule(btn, module) {
            document.querySelectorAll('.module-filter').forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-ghost');
            });
            if (btn) { btn.classList.add('btn-primary'); btn.classList.remove('btn-ghost'); }

            document.querySelectorAll('.module-group').forEach(g => {
                g.style.display = (!module || g.dataset.module === module) ? '' : 'none';
            });
        }

        function filterPerms(q) {
            const query = q.toLowerCase();
            document.querySelectorAll('.perm-row').forEach(row => {
                row.style.display = row.dataset.slug.includes(query) ? '' : 'none';
            });
        }

        // ── Ouvrir modal pour un module spécifique ────────────────────
        function openCreatePermForModule(module) {
            document.getElementById('perm-modal-title').textContent = '🔑 Nouvelle permission';
            document.getElementById('perm-id').value = '';
            document.getElementById('perm-name').value = '';
            document.getElementById('perm-desc').value = '';
            const sel = document.getElementById('perm-module');
            [...sel.options].forEach(o => { if (o.value === module) o.selected = true; });
            updateSlug();
            // reset checkboxes
            document.querySelectorAll('[name="perm_roles[]"]').forEach(c => c.checked = false);
            openModal('modal-create-perm');
        }

        function openEditPerm(id, name, slug, desc) {
            document.getElementById('perm-modal-title').textContent = '✏️ Modifier la permission';
            document.getElementById('perm-id').value = id;
            document.getElementById('perm-name').value = name;
            document.getElementById('perm-slug-preview').value = slug;
            document.getElementById('perm-desc').value = desc;
            openModal('modal-create-perm');
        }

        // ── CRUD ──────────────────────────────────────────────────────
        async function savePerm() {
            const id    = document.getElementById('perm-id').value;
            const roles = [...document.querySelectorAll('[name="perm_roles[]"]:checked')]
                .map(c => parseInt(c.value));
            const body  = {
                name:        document.getElementById('perm-name').value,
                slug:        document.getElementById('perm-slug-preview').value,
                description: document.getElementById('perm-desc').value,
                roles,
            };

            try {
                const url    = id ? `/acl/permissions/${id}` : '/acl/permissions';
                const method = id ? 'PUT' : 'POST';
                const data   = await apiCall(url, method, body);
                closeModal('modal-create-perm');
                showToast(data.message);
                setTimeout(() => location.reload(), 800);
            } catch (e) { showToast(e.message, 'error'); }
        }

        async function deletePerm(id, slug, btn) {
            if (! confirm(`Supprimer la permission "${slug}" ?`)) return;
            try {
                const data = await apiCall(`/acl/permissions/${id}`, 'DELETE');
                const row  = btn.closest('.perm-row');
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s';
                setTimeout(() => row.remove(), 300);
                showToast(data.message);
            } catch (e) { showToast(e.message, 'error'); }
        }
    </script>
@endsection