@extends('acl::layouts.app')

@section('title', 'Audit Log')
@section('page-title', '📋 Audit Log')

@section('topbar-actions')
    <div class="search-wrap">
        <span class="search-icon">🔍</span>
        <input class="input search-input" style="width:220px;" placeholder="Rechercher..."
               oninput="filterTable(this.value)">
    </div>
    <button class="btn btn-ghost btn-sm" onclick="exportCSV()">📥 Exporter CSV</button>
@endsection

@section('content')

    {{-- Filtres --}}
    <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
        <select class="input" style="width:170px;" id="filter-action" onchange="applyFilters()">
            <option value="">Toutes les actions</option>
            <option value="ASSIGN_ROLE">ASSIGN_ROLE</option>
            <option value="REVOKE_ROLE">REVOKE_ROLE</option>
            <option value="SYNC_ROLES">SYNC_ROLES</option>
            <option value="CREATE_ROLE">CREATE_ROLE</option>
            <option value="DELETE_ROLE">DELETE_ROLE</option>
            <option value="CREATE_PERM">CREATE_PERM</option>
            <option value="DELETE_PERM">DELETE_PERM</option>
            <option value="SYNC_PERMS">SYNC_PERMS</option>
            <option value="ACCESS_DENIED">ACCESS_DENIED</option>
        </select>

        <select class="input" style="width:140px;" id="filter-result" onchange="applyFilters()">
            <option value="">Tous résultats</option>
            <option value="OK">✓ Succès</option>
            <option value="403">✗ Refusé</option>
        </select>

        <input type="date" class="input" style="width:160px;" id="filter-date-from"
               onchange="applyFilters()">
        <input type="date" class="input" style="width:160px;" id="filter-date-to"
               onchange="applyFilters()">

        <button class="btn btn-ghost btn-sm" onclick="resetFilters()">✕ Réinitialiser</button>

        <span style="margin-left:auto;font-size:12px;color:var(--muted);align-self:center;">
    <span id="row-count">{{ $logs->total() }}</span> entrées
  </span>
    </div>

    {{-- Table --}}
    <div class="card">
        <table class="table" id="audit-table">
            <thead>
            <tr>
                <th>Horodatage</th>
                <th>Action</th>
                <th>Effectué par</th>
                <th>Cible</th>
                <th>Détail</th>
                <th>IP</th>
                <th>Résultat</th>
            </tr>
            </thead>
            <tbody id="audit-tbody">
            @forelse($logs as $log)
                <tr class="audit-row"
                    data-action="{{ $log->action }}"
                    data-result="{{ $log->result }}"
                    data-date="{{ $log->created_at->format('Y-m-d') }}">
                    <td class="mono" style="font-size:12px;color:var(--muted);white-space:nowrap;">
                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                        <div style="font-size:10px;margin-top:2px;color:var(--border);">
                            {{ $log->created_at->diffForHumans() }}
                        </div>
                    </td>
                    <td>
                        @php
                            $actionColors = [
                              'ASSIGN_ROLE'  => 'rgba(34,211,165,0.1);color:var(--green)',
                              'REVOKE_ROLE'  => 'rgba(244,63,94,0.1);color:var(--red)',
                              'SYNC_ROLES'   => 'rgba(251,191,36,0.1);color:var(--yellow)',
                              'CREATE_ROLE'  => 'rgba(99,102,241,0.1);color:var(--accent2)',
                              'DELETE_ROLE'  => 'rgba(244,63,94,0.1);color:var(--red)',
                              'CREATE_PERM'  => 'rgba(99,102,241,0.1);color:var(--accent2)',
                              'DELETE_PERM'  => 'rgba(244,63,94,0.1);color:var(--red)',
                              'SYNC_PERMS'   => 'rgba(251,191,36,0.1);color:var(--yellow)',
                              'ACCESS_DENIED'=> 'rgba(244,63,94,0.1);color:var(--red)',
                            ];
                            $style = $actionColors[$log->action] ?? 'rgba(99,102,241,0.1);color:var(--accent2)';
                        @endphp
                        <span class="chip" style="background:{{ $style }}">{{ $log->action }}</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div class="avatar" style="width:28px;height:28px;font-size:11px;
                 background:linear-gradient(135deg,#6366f1,#a78bfa);">
                                {{ strtoupper(substr($log->causer?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-size:13px;font-weight:600;">
                                    {{ $log->causer?->name ?? 'Système' }}
                                </div>
                                <div class="mono" style="font-size:10px;color:var(--muted);">
                                    {{ $log->causer?->email ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($log->subject)
                            <div style="font-size:13px;font-weight:600;">{{ $log->subject?->name ?? '—' }}</div>
                            <div class="mono" style="font-size:10px;color:var(--muted);">
                                {{ $log->subject?->email ?? '' }}
                            </div>
                        @else
                            <span style="color:var(--muted);">—</span>
                        @endif
                    </td>
                    <td class="mono" style="font-size:12px;">
                        @if($log->properties)
                            @foreach($log->properties as $key => $val)
                                <span style="color:var(--muted);">{{ $key }}:</span>
                                <span style="color:var(--accent2);">{{ is_array($val) ? implode(', ', $val) : $val }}</span><br>
                            @endforeach
                        @else
                            <span style="color:var(--muted);">—</span>
                        @endif
                    </td>
                    <td class="mono" style="font-size:12px;color:var(--muted);">
                        {{ $log->ip_address ?? '—' }}
                    </td>
                    <td>
                        @if($log->result === 'OK')
                            <span class="badge badge-green">✓ OK</span>
                        @else
                            <span class="badge badge-red">✗ {{ $log->result }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
                        Aucune entrée dans le journal
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
        <div style="margin-top:16px;display:flex;justify-content:space-between;align-items:center;">
  <span style="font-size:12px;color:var(--muted);">
    Affichage {{ $logs->firstItem() }}–{{ $logs->lastItem() }} sur {{ $logs->total() }}
  </span>
            <div style="display:flex;gap:6px;">
                @if($logs->onFirstPage())
                    <button class="btn btn-ghost btn-sm" disabled style="opacity:0.4;">← Précédent</button>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="btn btn-ghost btn-sm">← Précédent</a>
                @endif
                @foreach($logs->getUrlRange(1, $logs->lastPage()) as $page => $url)
                    <a href="{{ $url }}"
                       class="btn btn-sm {{ $page == $logs->currentPage() ? 'btn-primary' : 'btn-ghost' }}">
                        {{ $page }}
                    </a>
                @endforeach
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="btn btn-ghost btn-sm">Suivant →</a>
                @else
                    <button class="btn btn-ghost btn-sm" disabled style="opacity:0.4;">Suivant →</button>
                @endif
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    <script>
        // ── Filtres client-side ───────────────────────────────────────
        function applyFilters() {
            const action   = document.getElementById('filter-action').value.toLowerCase();
            const result   = document.getElementById('filter-result').value.toLowerCase();
            const dateFrom = document.getElementById('filter-date-from').value;
            const dateTo   = document.getElementById('filter-date-to').value;
            let count = 0;

            document.querySelectorAll('.audit-row').forEach(row => {
                const rAction = row.dataset.action.toLowerCase();
                const rResult = row.dataset.result.toLowerCase();
                const rDate   = row.dataset.date;

                const matchAction = !action || rAction.includes(action);
                const matchResult = !result || rResult.includes(result);
                const matchFrom   = !dateFrom || rDate >= dateFrom;
                const matchTo     = !dateTo   || rDate <= dateTo;

                const visible = matchAction && matchResult && matchFrom && matchTo;
                row.style.display = visible ? '' : 'none';
                if (visible) count++;
            });

            document.getElementById('row-count').textContent = count;
        }

        function filterTable(query) {
            const q = query.toLowerCase();
            let count = 0;
            document.querySelectorAll('.audit-row').forEach(row => {
                const match = row.textContent.toLowerCase().includes(q);
                row.style.display = match ? '' : 'none';
                if (match) count++;
            });
            document.getElementById('row-count').textContent = count;
        }

        function resetFilters() {
            ['filter-action','filter-result','filter-date-from','filter-date-to'].forEach(id => {
                document.getElementById(id).value = '';
            });
            document.querySelectorAll('.audit-row').forEach(r => r.style.display = '');
            document.getElementById('row-count').textContent =
                document.querySelectorAll('.audit-row').length;
        }

        // ── Export CSV ────────────────────────────────────────────────
        function exportCSV() {
            const rows = [['Horodatage','Action','Effectué par','Cible','IP','Résultat']];

            document.querySelectorAll('.audit-row').forEach(row => {
                if (row.style.display === 'none') return;
                const cells = row.querySelectorAll('td');
                rows.push([
                    cells[0].textContent.trim().split('\n')[0],
                    cells[1].textContent.trim(),
                    cells[2].textContent.trim().replace(/\s+/g,' '),
                    cells[3].textContent.trim().replace(/\s+/g,' '),
                    cells[5].textContent.trim(),
                    cells[6].textContent.trim(),
                ]);
            });

            const csv  = rows.map(r => r.map(c => `"${c}"`).join(',')).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url  = URL.createObjectURL(blob);
            const a    = Object.assign(document.createElement('a'), {
                href: url,
                download: `audit-log-${new Date().toISOString().split('T')[0]}.csv`,
            });
            a.click();
            URL.revokeObjectURL(url);
            showToast('Export CSV téléchargé');
        }
    </script>
@endsection