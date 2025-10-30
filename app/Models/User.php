<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ===== Generar slug automáticamente =====
    protected static function booted()
    {
        static::creating(function ($user) {
            // Si el slug no se ha asignado manualmente
            if (empty($user->slug)) {
                $user->slug = Str::slug($user->name . '-' . uniqid());
            }
        });
    }

    /**
     * Devuelve las iniciales del usuario (por ejemplo: "LO").
     */
    public function getInitialsAttribute(): string
    {
        $nameParts = explode(' ', trim($this->name));
        $initials = '';

        foreach ($nameParts as $part) {
            $initials .= strtoupper(substr($part, 0, 1));
        }

        return substr($initials, 0, 2) ?: '?';
    }

    /**
     * Verifica si el usuario tiene una foto almacenada localmente.
     */
    public function getHasLocalPhotoAttribute(): bool
    {
        return !empty($this->profile_photo_path)
            && file_exists(storage_path('app/public/' . $this->profile_photo_path));
    }

    /**
     * Devuelve el color de fondo aleatorio pero estable según el nombre.
     * (Se basa en un hash del nombre, así que siempre es el mismo color para ese usuario)
     */
    public function getAvatarColorsAttribute(): array
    {
        $colors = [
            ['#FFE5E5', '#D30035'], // Fondo rosado claro → texto rojo fuerte
            ['#E5FAFF', '#1D61D0'], // Fondo celeste claro → texto azul fuerte
            ['#E5F9E7', '#009900'], // Fondo verde claro → texto verde fuerte
            ['#FFB74D', '#000000'], // Fondo naranja claro → texto negro
            ['#4DB6AC', '#00695C'], // Turquesa → verde azulado oscuro
            ['#9575CD', '#4527A0'], // Morado → púrpura oscuro
            ['#F06292', '#AD1457'], // Rosado → rojo frambuesa
            ['#7986CB', '#283593'], // Azul grisáceo → azul profundo
        ];

        $index = crc32($this->name) % count($colors);

        return [
            'background' => $colors[$index][0],
            'color' => $colors[$index][1],
        ];
    }

    public function getRoleListAttribute()
    {
        return $this->getRoleNames()->implode(', ');
    }
}
