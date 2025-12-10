<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanySetting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'legal_name',
        'ruc',
        'slogan',
        'email',
        'phone',
        'address',
        'website',
        'social_links',
        'logo_path',
        'about',
        'support_email',
        'support_phone',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'youtube_url',
        'tiktok_url',
        'linkedin_url',
        'primary_color',
        'secondary_color',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function (self $model): void {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function (self $model): void {
            if (!$model->isForceDeleting() && Auth::check()) {
                $model->deleted_by = Auth::id();
                $model->saveQuietly();
            }
        });

        static::saved(function (): void {
            Cache::forget('company_settings');
        });

        static::deleted(function (): void {
            Cache::forget('company_settings');
        });
    }

    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo_path) {
            return asset('logo.png');
        }

        if (Str::startsWith($this->logo_path, ['http://', 'https://'])) {
            return $this->logo_path;
        }

        return Storage::disk('public')->url($this->logo_path);
    }

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

    public function socialLink(string $key, string $default = ''): string
    {
        $links = $this->social_links ?? [];
        return $links[$key] ?? $default;
    }
}
