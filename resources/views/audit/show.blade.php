@extends('acl::layouts.app')

@section('title', 'Détail Audit #' . $auditLog->id)
@section('page-title', '📋 Détail du log')

@section('page-badge')
    @if($auditLog->isSuccess())
        <span class="badge badge-green">✓ {{ $auditLog->result }}</span>
    @else
        <span class="badge badge-red">✗ {{ $auditLog->result }}</span>
    @endif
@endsection

@section('breadcrumb')
    <a href="{{ route('acl.audit.index') }}">Audit Log</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">#{{ $auditLog->id }}</span>
@endsection

@section('topbar-actions')
    <a href="{{ route('acl.audit.index') }}" class="btn btn-ghost btn-sm">
        ← Retour
    </a>
    <button class="btn btn-danger btn-sm"
            onclick="deleteLog({{ $auditLog->id }})">
        🗑 Supprimer
    </button>
@endsection

@section('content')

    @php
        $actionColors = [
          'ASSIGN_ROLE'   => ['rgba(34,211,165,0.1)',  'rgba(34,211,165,0.25)',  'var(--green)'],
          'REVOKE_ROLE'   => ['rgba(244,63,94,0.1)',   'rgba(244,63,94,0.25)',   'var(--red)'],
          'SYNC_ROLES'    => ['rgba(251,191,36,0.1)',  'rgba(251,191,36,0.25)',  'var(--yellow)'],
          'CREATE_ROLE'   => ['rgba(99,102,241,0.1)',  'rgba(99,102,241,0.25)',  'var(--accent2)'],
          'UPDATE_ROLE'   => ['rgba(99,102,241,0.1)',  'rgba(99,102,241,0.25)',  'var(--accent2)'],
          'DELETE_ROLE'   => ['rgba(244,63,94,0.1)',   'rgba(244,63,94,0.25)',   'var(--red)'],
          'CREATE_PERM'   => ['rgba(99,102,241,0.1)',  'rgba(99,102,241,0.25)',  'var(--accent2)'],
          'UPDATE_PERM'   => ['rgba(99,102,241,0.1)',  'rgba(99,102,241,0.25)',  'var(--accent2)'],
          'DELETE_PERM'   => ['rgba(244,63,94,0.1)',   'rgba(244,63,94,0.25)',   'var(--red)'],
          'SYNC_PERMS'    => ['rgba(251,191,36,0.1)',  'rgba(251,191,36,0.25)',  'var(--yellow)'],
          'ACCESS_DENIED' => ['rgba(244,63,94,0.1)',   'rgba(244,63,94,0.25)',   'var(--red)'],
          'PURGE_LOGS'    => ['rgba(251,191,36,0.1)',  'rgba(251,191,36,0.25)',  'var(--yellow)'],
        ];
        [$bgAction, $borderAction, $colorAction] = $actionColors[$auditLog->action]
            ?? ['rgba(99,102,241,0.1)', 'rgba(99,102,241,0.25)', 'var(--accent2)'];
    @endphp

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        {{-- ── Informations principales ──────────────────────────── --}}
        <div style="grid-column:1/-1;">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Informations générales</div>
                    <span class="mono" style="font-size:12px;color:var(--muted);">
          ID #{{ $auditLog->id }}
        </span>
                </div>
                <div style="padding:24px;display:grid;grid-template-columns:repeat(4,1fr);gap:20px;">

                    {{-- Action --}}
                    <div>
                        <div class="label">Action</div>
                        <span class="chip"
                              style="background:{{ $bgAction }};
                       border-color:{{ $borderAction }};
                       color:{{ $colorAction }};
                       font-size:13px;padding:5px 12px;">
            {{ $auditLog->action }}
          </span>
                    </div>

                    {{-- Résultat --}}
                    <div>
                        <div class="label">Résultat</div>
                        @if($auditLog->isSuccess())
                            <span class="badge badge-green" style="font-size:13px;padding:5px 12px;">
              ✓ {{ $auditLog->result }}
            </span>
                        @else
                            <span class="badge badge-red" style="font-size:13px;padding:5px 12px;">
              ✗ {{ $auditLog->result }}
            </span>
                        @endif
                    </div>

                    {{-- Date --}}
                    <div>
                        <div class="label">Horodatage</div>
                        <div class="mono" style="font-size:13px;">
                            {{ $auditLog->created_at->format('d/m/Y H:i:s') }}
                        </div>
                        <div style="font-size:11px;color:var(--muted);margin-top:3px;">
                            {{ $auditLog->created_at->diffForHumans() }}
                        </div>
                    </div>

                    {{-- IP --}}
                    <div>
                        <div class="label">Adresse IP</div>
                        <div class="mono" style="font-size:13px;color:var(--green);">
                            {{ $auditLog->ip_address ?? '—' }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ── Effectué par ───────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">👤 Effectué par</div>
            </div>
            <div style="padding:20px;">
                @if($auditLog->causer)
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                        <div class="avatar avatar-lg"
                             style="background:linear-gradient(135deg,#6366f1,#a78bfa);
                      width:48px;height:48px;font-size:18px;">
                            {{ strtoupper(substr($auditLog->causer->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:16px;font-weight:800;">
                                {{ $auditLog->causer->name }}
                            </div>
                            <div class="mono" style="font-size:12px;color:var(--muted);">
                                {{ $auditLog->causer->email }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">ID utilisateur</span>
                            <span class="mono" style="font-size:12px;">#{{ $auditLog->causer->id }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">Type</span>
                            <span class="badge badge-purple">{{ $auditLog->causer->user_type ?? 'N/A' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">Statut</span>
                            @php $status = $auditLog->causer->status ?? 'active'; @endphp
                            <span class="badge {{ $status === 'active' ? 'badge-green' : 'badge-red' }}">
              {{ $status }}
            </span>
                        </div>
                        <a href="{{ route('acl.users.index', ['search' => $auditLog->causer->email]) }}"
                           class="btn btn-ghost btn-sm" style="justify-content:center;margin-top:4px;">
                            👁 Voir le profil
                        </a>
                    </div>
                @else
                    <div class="empty-state" style="padding:30px 20px;">
                        <div class="empty-state-icon">🤖</div>
                        <div class="empty-state-title">Action système</div>
                        <div class="empty-state-text">
                            Effectuée automatiquement sans utilisateur connecté.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Cible ──────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">🎯 Utilisateur cible</div>
            </div>
            <div style="padding:20px;">
                @if($auditLog->subject)
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                        <div class="avatar avatar-lg"
                             style="background:linear-gradient(135deg,#22d3a5,#06b6d4);
                      width:48px;height:48px;font-size:18px;">
                            {{ strtoupper(substr($auditLog->subject->name, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-size:16px;font-weight:800;">
                                {{ $auditLog->subject->name }}
                            </div>
                            <div class="mono" style="font-size:12px;color:var(--muted);">
                                {{ $auditLog->subject->email }}
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:10px;">
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">ID utilisateur</span>
                            <span class="mono" style="font-size:12px;">#{{ $auditLog->subject->id }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">Rôles actuels</span>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                                @forelse($auditLog->subject->roles ?? [] as $role)
                                    <span class="badge badge-purple">{{ $role->slug }}</span>
                                @empty
                                    <span style="font-size:11px;color:var(--muted);">Aucun rôle</span>
                                @endforelse
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;
                      padding:8px 12px;background:var(--surface2);
                      border-radius:7px;border:1px solid var(--border);">
                            <span style="font-size:12px;color:var(--muted);">Statut</span>
                            @php $subjectStatus = $auditLog->subject->status ?? 'active'; @endphp
                            <span class="badge {{ $subjectStatus === 'active' ? 'badge-green' : 'badge-red' }}">
              {{ $subjectStatus }}
            </span>
                        </div>
                        <a href="{{ route('acl.users.index', ['search' => $auditLog->subject->email]) }}"
                           class="btn btn-ghost btn-sm" style="justify-content:center;margin-top:4px;">
                            👁 Voir le profil
                        </a>
                    </div>
                @else
                    <div class="empty-state" style="padding:30px 20px;">
                        <div class="empty-state-icon">—</div>
                        <div class="empty-state-title">Pas de cible</div>
                        <div class="empty-state-text">
                            Cette action ne cible pas un utilisateur spécifique.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Propriétés / Données ───────────────────────────────── --}}
        <div class="card" style="grid-column:1/-1;">
            <div class="card-header">
                <div class="card-title">⚙️ Propriétés de l'action</div>
                <button class="btn btn-ghost btn-xs" onclick="copyJson()" id="copy-btn">
                    📋 Copier JSON
                </button>
            </div>
            <div style="padding:20px;">
                @if($auditLog->properties && count($auditLog->properties))

                    {{-- Vue tableau des propriétés --}}
                    <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;">
                        @foreach($auditLog->properties as $key => $value)
                            <div style="display:flex;align-items:flex-start;gap:12px;
                      padding:12px 16px;background:var(--surface2);
                      border:1px solid var(--border);border-radius:8px;">
                                <div style="min-width:160px;">
              <span class="mono" style="font-size:12px;color:var(--accent2);">
                {{ $key }}
              </span>
                                </div>
                                <div style="flex:1;">
                                    @if(is_array($value))
                                        {{-- Tableaux : afficher comme chips --}}
                                        @if(array_is_list($value))
                                            <div style="display:flex;flex-wrap:wrap;gap:5px;">
                                                @forelse($value as $item)
                                                    <span class="chip">{{ $item }}</span>
                                                @empty
                                                    <span style="font-size:12px;color:var(--muted);">[]</span>
                                                @endforelse
                                            </div>
                                        @else
                                            {{-- Objet associatif --}}
                                            <div style="display:flex;flex-direction:column;gap:4px;">
                                                @foreach($value as $k => $v)
                                                    <div style="display:flex;gap:8px;">
                                                        <span class="mono" style="font-size:11px;color:var(--muted);">{{ $k }}:</span>
                                                        <span class="mono" style="font-size:11px;color:var(--text);">{{ $v }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    @elseif(is_bool($value))
                                        <span class="badge {{ $value ? 'badge-green' : 'badge-red' }}">
                  {{ $value ? 'true' : 'false' }}
                </span>
                                    @elseif(is_numeric($value))
                                        <span class="mono" style="font-size:13px;color:var(--yellow);">
                  {{ $value }}
                </span>
                                    @else
                                        <span class="mono" style="font-size:13px;color:var(--green);">
                  "{{ $value }}"
                </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Vue JSON brut --}}
                    <div>
                        <div style="font-size:11px;font-weight:600;color:var(--muted);
                      margin-bottom:8px;font-family:'JetBrains Mono',monospace;
                      letter-spacing:1px;text-transform:uppercase;">
                            JSON brut
                        </div>
                        <pre id="json-raw"
                             style="background:var(--surface2);border:1px solid var(--border);
                      border-radius:8px;padding:16px;
                      font-family:'JetBrains Mono',monospace;font-size:12px;
                      color:var(--green);overflow-x:auto;line-height:1.6;">{{ json_encode($auditLog->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>

                @else
                    <div class="empty-state" style="padding:30px;">
                        <div class="empty-state-icon">📭</div>
                        <div class="empty-state-title">Aucune propriété</div>
                        <div class="empty-state-text">
                            Cette action n'a pas de données supplémentaires.
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- ── User Agent ─────────────────────────────────────────── --}}
        @if($auditLog->user_agent)
            <div class="card" style="grid-column:1/-1;">
                <div class="card-header">
                    <div class="card-title">🌐 Informations réseau</div>
                </div>
                <div style="padding:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <div class="label">Adresse IP</div>
                        <div style="display:flex;align-items:center;gap:8px;
                    padding:10px 14px;background:var(--surface2);
                    border:1px solid var(--border);border-radius:8px;">
                            <span style="font-size:16px;">🌍</span>
                            <span class="mono" style="font-size:13px;color:var(--green);">
            {{ $auditLog->ip_address ?? '—' }}
          </span>
                        </div>
                    </div>
                    <div>
                        <div class="label">User Agent</div>
                        <div style="padding:10px 14px;background:var(--surface2);
                    border:1px solid var(--border);border-radius:8px;">
          <span class="mono" style="font-size:11px;color:var(--muted);
                                    word-break:break-all;line-height:1.5;">
            {{ $auditLog->user_agent }}
          </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ── Navigation entre logs ──────────────────────────────── --}}
        <div style="grid-column:1/-1;display:flex;justify-content:space-between;
              align-items:center;padding:4px 0;">

            {{-- Log précédent --}}
            @php
                $prev = \MecenePhrygien\LaravelAcl\Models\AuditLog::where('id', '<', $auditLog->id)
                          ->latest('id')->first();
                $next = \MecenePhrygien\LaravelAcl\Models\AuditLog::where('id', '>', $auditLog->id)
                          ->oldest('id')->first();
            @endphp

            @if($prev)
                <a href="{{ route('acl.audit.show', $prev) }}" class="btn btn-ghost btn-sm">
                    ← Log précédent
                    <span class="mono" style="color:var(--muted);">#{{ $prev->id }}</span>
                </a>
            @else
                <span></span>
            @endif

            <a href="{{ route('acl.audit.index') }}" class="btn btn-ghost btn-sm">
                ☰ Tous les logs
            </a>

            @if($next)
                <a href="{{ route('acl.audit.show', $next) }}" class="btn btn-ghost btn-sm">
                    <span class="mono" style="color:var(--muted);">#{{ $next->id }}</span>
                    Log suivant →
                </a>
            @else
                <span></span>
            @endif

        </div>

    </div>

    {{-- ── Modal confirmation suppression ───────────────────────── --}}
    <div class="modal-overlay" id="modal-delete">
        <div class="modal modal-sm">
            <div class="modal-header">
                <div class="modal-title">🗑 Confirmer la suppression</div>
                <button class="btn btn-ghost btn-sm" onclick="closeModal('modal-delete')">✕</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    ⚠️ Cette action est irréversible. Le log #{{ $auditLog->id }} sera
                    définitivement supprimé.
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" onclick="closeModal('modal-delete')">
                    Annuler
                </button>
                <button class="btn btn-danger" onclick="confirmDelete(this)">
                    🗑 Supprimer définitivement
                </button>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // ── Copier JSON ───────────────────────────────────────────────
        function copyJson() {
            const text = document.getElementById('json-raw')?.textContent ?? '';
            navigator.clipboard.writeText(text).then(() => {
                const btn = document.getElementById('copy-btn');
                btn.textContent = '✅ Copié !';
                setTimeout(() => btn.textContent = '📋 Copier JSON', 2000);
            });
        }

        // ── Supprimer le log ──────────────────────────────────────────
        function deleteLog(id) {
            openModal('modal-delete');
        }

        async function confirmDelete(btn) {
            setLoading(btn, true);
            try {
                await apiCall('{{ route("acl.audit.destroy", $auditLog) }}', 'DELETE');
                showToast('Log supprimé');
                setTimeout(() => window.location.href = '{{ route("acl.audit.index") }}', 800);
            } catch (e) {
                setLoading(btn, false);
                showToast(e.message, 'error');
            }
        }
    </script>
@endsection