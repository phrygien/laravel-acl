<?php

namespace MecenePhrygien\LaravelAcl\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use MecenePhrygien\LaravelAcl\Models\AuditLog;

class AuditController extends Controller
{
    // ─── Index ────────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = AuditLog::with(['causer', 'subject'])->latest();

        // Filtre action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filtre résultat
        if ($request->filled('result')) {
            $query->where('result', $request->result);
        }

        // Filtre date de début
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        // Filtre date de fin
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filtre utilisateur (causer ou subject)
        if ($request->filled('user_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('causer_id', $request->user_id)
                    ->orWhere('subject_id', $request->user_id);
            });
        }

        // Recherche texte libre dans properties
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $term = '%'.$request->search.'%';
                $q->where('action', 'like', $term)
                    ->orWhere('ip_address', 'like', $term)
                    ->orWhere('properties', 'like', $term)
                    ->orWhereHas('causer', fn($u) =>
                    $u->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                    )
                    ->orWhereHas('subject', fn($u) =>
                    $u->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                    );
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Stats pour les badges en haut de page
        $stats = $this->getStats();

        // Liste des actions distinctes pour le filtre select
        $actions = AuditLog::distinct()->orderBy('action')->pluck('action');

        // Liste des users pour le filtre
        $users = app(config('acl.user_model'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('acl::audit.index', compact(
            'logs',
            'stats',
            'actions',
            'users',
        ));
    }

    // ─── Show ─────────────────────────────────────────────────

    public function show(AuditLog $auditLog): View
    {
        $auditLog->load(['causer', 'subject']);

        return view('acl::audit.show', compact('auditLog'));
    }

    // ─── Destroy (single) ─────────────────────────────────────

    public function destroy(AuditLog $auditLog): JsonResponse
    {
        $auditLog->delete();

        return response()->json([
            'message' => 'Entrée supprimée.',
        ]);
    }

    // ─── Purge (bulk) ─────────────────────────────────────────

    public function purge(Request $request): JsonResponse
    {
        $data = $request->validate([
            'older_than_days' => 'required|integer|min:1|max:3650',
        ]);

        $count = AuditLog::where(
            'created_at', '<', now()->subDays($data['older_than_days'])
        )->delete();

        AuditLog::record(
            action:     AuditLog::ACTION_PURGE_LOGS,
            properties: [
                'deleted_count'   => $count,
                'older_than_days' => $data['older_than_days'],
            ],
        );

        return response()->json([
            'message' => "{$count} entrée(s) supprimée(s).",
            'count'   => $count,
        ]);
    }

    // ─── Export CSV ───────────────────────────────────────────

    public function export(Request $request): Response
    {
        $query = AuditLog::with(['causer', 'subject'])->latest();

        // Appliquer les mêmes filtres que index
        if ($request->filled('action'))    $query->where('action', $request->action);
        if ($request->filled('result'))    $query->where('result', $request->result);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);

        $logs = $query->limit(10000)->get(); // sécurité : max 10k lignes

        $csv = $this->buildCsv($logs);

        $filename = 'audit-log-'.now()->format('Y-m-d').'.csv';

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ─── Stats (AJAX) ─────────────────────────────────────────

    public function stats(): JsonResponse
    {
        return response()->json($this->getStats());
    }

    // ─── Méthodes privées ─────────────────────────────────────

    private function getStats(): array
    {
        $base = AuditLog::query();

        return [
            'total'        => (clone $base)->count(),
            'today'        => (clone $base)->whereDate('created_at', today())->count(),
            'denied'       => (clone $base)->where('action', AuditLog::ACTION_ACCESS_DENIED)->count(),
            'denied_today' => (clone $base)
                ->where('action', AuditLog::ACTION_ACCESS_DENIED)
                ->whereDate('created_at', today())
                ->count(),
            'by_action'    => (clone $base)
                ->selectRaw('action, count(*) as total')
                ->groupBy('action')
                ->orderByDesc('total')
                ->pluck('total', 'action'),
            'by_result'    => (clone $base)
                ->selectRaw('result, count(*) as total')
                ->groupBy('result')
                ->pluck('total', 'result'),
        ];
    }

    private function buildCsv($logs): string
    {
        $headers = [
            'ID',
            'Horodatage',
            'Action',
            'Effectué par (nom)',
            'Effectué par (email)',
            'Cible (nom)',
            'Cible (email)',
            'Propriétés',
            'Résultat',
            'IP',
            'User Agent',
        ];

        $rows   = [];
        $rows[] = implode(',', array_map(
            fn($h) => '"'.str_replace('"', '""', $h).'"',
            $headers
        ));

        foreach ($logs as $log) {
            $rows[] = implode(',', array_map(
                fn($v) => '"'.str_replace('"', '""', (string) $v).'"',
                [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->action,
                    $log->causer?->name  ?? 'Système',
                    $log->causer?->email ?? '',
                    $log->subject?->name  ?? '—',
                    $log->subject?->email ?? '',
                    $log->properties ? json_encode($log->properties, JSON_UNESCAPED_UNICODE) : '',
                    $log->result,
                    $log->ip_address ?? '',
                    $log->user_agent ?? '',
                ]
            ));
        }

        // BOM UTF-8 pour Excel
        return "\xEF\xBB\xBF" . implode("\n", $rows);
    }
}