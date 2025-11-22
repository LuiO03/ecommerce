<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable, HasProfilePhoto, HasRoles;

    protected $fillable = [
        'name',
        'last_name',
        'email',
        'slug',
        'password',
        'address',
        'dni',
        'phone',
        'image',
        'status',
        'last_login',
        'last_password_update',
        'failed_attempts',
        'blocked_until',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
        'initials',
        'avatar_colors',
        'role_list',
        'has_local_photo',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'last_password_update' => 'datetime',
            'blocked_until' => 'datetime',
            'status' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /* ============================================================
     |  BOOT METHODS
     |============================================================ */

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->slug)) {
                $user->slug = self::generateUniqueSlug($user->name);
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('name')) {
                $user->slug = self::generateUniqueSlug($user->name, $user->id);
            }
        });
    }

    /* ============================================================
     |  SCOPES
     |============================================================ */

    public function scopeForSelect($query)
    {
        return $query->select('id', 'name', 'email')->orderBy('name');
    }

    public function scopeForTable($query)
    {
        return $query->select('id', 'name', 'email', 'status', 'created_at')->orderByDesc('id');
    }

    /* ============================================================
     |  SLUG SYSTEM (igual que Family)
     |============================================================ */

    public static function generateUniqueSlug($name, $id = null)
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $count = 1;

        while (
            self::where('slug', $slug)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->exists()
        ) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /* ============================================================
     |  RELACIONES DE AUDITORÍA
     |============================================================ */

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /* ============================================================
     |  ACCESSORS
     |============================================================ */

    public function getInitialsAttribute(): string
    {
        $initials = collect(explode(' ', trim($this->name)))
            ->map(fn($part) => strtoupper(substr($part, 0, 1)))
            ->take(2)
            ->implode('');

        return $initials ?: '?';
    }

    public function getHasLocalPhotoAttribute(): bool
    {
        return !empty($this->profile_photo_path) &&
            file_exists(storage_path('app/public/' . $this->profile_photo_path));
    }

    public function getAvatarColorsAttribute(): array
    {
        // Paletas estables según nombre
        $colors = [
            ['#FFE5E5', '#D30035'],
            ['#E5FAFF', '#1D61D0'],
            ['#E5F9E7', '#009900'],
            ['#FFB74D', '#000000'],
            ['#4DB6AC', '#00695C'],
            ['#9575CD', '#4527A0'],
            ['#F06292', '#AD1457'],
            ['#7986CB', '#283593'],
        ];

        $index = crc32($this->name) % count($colors);

        return [
            'background' => $colors[$index][0],
            'color'      => $colors[$index][1],
        ];
    }

    public function getRoleListAttribute()
    {
        return $this->getRoleNames()->implode(', ');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? Storage::url($this->image) : asset('images/no-image.png');
    }
}
