<?php

namespace MecenePhrygien\LaravelAcl\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public const UPDATED_AT = null; // pas de updated_at

    protected $table = 'acl_audit_logs';

    protected $fillable = [
        'action',
        'causer_id',
        'subject_id',
        'properties',
        'result',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Actions disponibles ──────────────────────────────────

    public const ACTION_ASSIGN_ROLE   = 'ASSIGN_ROLE';
    public const ACTION_REVOKE_ROLE   = 'REVOKE_ROLE';
    public const ACTION_SYNC_ROLES    = 'SYNC_ROLES';
    public const ACTION_CREATE_ROLE   = 'CREATE_ROLE';
    public const ACTION_UPDATE_ROLE   = 'UPDATE_ROLE';
    public const ACTION_DELETE_ROLE   = 'DELETE_ROLE';
    public const ACTION_CREATE_PERM   = 'CREATE_PERM';
    public const ACTION_UPDATE_PERM   = 'UPDATE_PERM';
    public const ACTION_DELETE_PERM   = 'DELETE_PERM';
    public const ACTION_SYNC_PERMS    = 'SYNC_PERMS';
    public const ACTION_ACCESS_DENIED = 'ACCESS_DENIED';

    // ─── Relations ────────────────────────────────────────────

    public function causer()
    {
        return $this->belongsTo(config('acl.user_model'), 'causer_id');
    }

    public function subject()
    {
        return $this->belongsTo(config('acl.user_model'), 'subject_id');
    }

    // ─── Scopes ───────────────────────────────────────────────

    public function scopeAction(Builder $q, string $action): Builder
    {
        return $q->where('action', $action);
    }

    public function scopeResult(Builder $q, string $result): Builder
    {
        return $q->where('result', $result);
    }

    public function scopeForUser(Builder $q, int $userId): Builder
    {
        return $q->where('causer_id', $userId)
            ->orWhere('subject_id', $userId);
    }

    public function scopeBetweenDates(Builder $q, string $from, string $to): Builder
    {
        return $q->whereBetween('created_at', [$from, $to.' 23:59:59']);
    }

    public function scopeRecent(Builder $q, int $days = 30): Builder
    {
        return $q->where('created_at', '>=', now()->subDays($days));
    }

    // ─── Factory : créer un log facilement ───────────────────

    public static function record(
        string  $action,
        ?int    $causerId  = null,
        ?int    $subjectId = null,
        array   $properties = [],
        string  $result    = 'OK',
    ): static {
        return static::create([
            'action'     => $action,
            'causer_id'  => $causerId  ?? auth()->id(),
            'subject_id' => $subjectId,
            'properties' => $properties,
            'result'     => $result,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->result === 'OK';
    }

    public function getColorAttribute(): string
    {
        return match(true) {
            str_contains($this->action, 'DELETE') ,
            str_contains($this->action, 'REVOKE') ,
                $this->action === self::ACTION_ACCESS_DENIED => 'red',
            str_contains($this->action, 'CREATE') ,
            str_contains($this->action, 'ASSIGN') => 'green',
            str_contains($this->action, 'UPDATE') ,
            str_contains($this->action, 'SYNC')   => 'yellow',
            default                                => 'purple',
        };
    }

    // src/Models/AuditLog.php — ajouter la constante
    public const ACTION_PURGE_LOGS = 'PURGE_LOGS';
}