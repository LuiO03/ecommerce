<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class AccessLog extends Model
{
    // No usamos updated_at
    public const UPDATED_AT = null;

    protected $table = 'access_logs';

    protected $fillable = [
        'user_id',
        'email',
        'action',
        'status',
        'ip_address',
        'user_agent',
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* =====================
     | Constantes
     ===================== */

    public const ACTION_LOGIN  = 'login';
    public const ACTION_LOGOUT = 'logout';

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED  = 'failed';

    /* =====================
     | Accessors
     ===================== */
    public function getStatusLabelAttribute(): string
    {
        return Str::ucfirst($this->status);
    }

    public function getActionLabelAttribute(): string
    {
        return Str::ucfirst($this->action);
    }

    /* =====================
     | Scopes
     ===================== */

    public function scopeAction($query, ?string $action)
    {
        if ($action) {
            $query->where('action', $action);
        }
    }

    public function scopeStatus($query, ?string $status)
    {
        if ($status) {
            $query->where('status', $status);
        }
    }

    public function scopeUser($query, $userId)
    {
        if ($userId) {
            $query->where('user_id', $userId);
        }
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        if ($from && $to) {
            $query->whereBetween('created_at', [$from, $to]);
        }
    }
}
