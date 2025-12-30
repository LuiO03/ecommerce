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

    public function getAgentInfoAttribute(): array
    {
        $ua = $this->user_agent ?? '';

        $browser = 'Desconocido';
        $browserIcon = 'ri-global-line';

        if (Str::contains($ua, 'Edg')) {
            $browser = 'Edge';
            $browserIcon = 'ri-edge-line';
        } elseif (Str::contains($ua, 'Chrome')) {
            $browser = 'Chrome';
            $browserIcon = 'ri-chrome-line';
        } elseif (Str::contains($ua, 'Firefox')) {
            $browser = 'Firefox';
            $browserIcon = 'ri-firefox-line';
        } elseif (Str::contains($ua, 'Safari')) {
            $browser = 'Safari';
            $browserIcon = 'ri-safari-line';
        }

        $os = 'Desconocido';
        $osIcon = 'ri-device-line';

        if (Str::contains($ua, 'Windows')) {
            $os = 'Windows';
            $osIcon = 'ri-windows-line';
        } elseif (Str::contains($ua, 'Macintosh')) {
            $os = 'MacOS';
            $osIcon = 'ri-apple-line';
        } elseif (Str::contains($ua, 'Android')) {
            $os = 'Android';
            $osIcon = 'ri-android-line';
        } elseif (Str::contains($ua, 'iPhone') || Str::contains($ua, 'iPad')) {
            $os = 'iOS';
            $osIcon = 'ri-apple-line';
        } elseif (Str::contains($ua, 'Linux')) {
            $os = 'Linux';
            $osIcon = 'ri-ubuntu-line';
        }

        return [
            'browser' => $browser,
            'browser_icon' => $browserIcon,
            'os' => $os,
            'os_icon' => $osIcon,
        ];
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
