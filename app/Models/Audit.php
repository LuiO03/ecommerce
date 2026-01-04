<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event',
        'description',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }

    protected static function booted(): void
    {
        static::creating(function (self $audit) {
            if (empty($audit->description)) {
                $audit->description = $audit->generateDescription();
            }
        });
    }

    public function getModelNameAttribute(): string
    {
        return $this->auditable_type ? class_basename($this->auditable_type) : 'Sistema';
    }

    public function generateDescription(): string
    {
        $event = (string) $this->event;
        $modelName = $this->model_name;

        $label = $this->guessMainLabel();
        $labelPart = $label ? " «{$label}»" : '';

        if ($event === 'created') {
            return "Se creó {$modelName}{$labelPart}";
        }

        if ($event === 'deleted') {
            return "Se eliminó {$modelName}{$labelPart}";
        }

        if ($event === 'updated') {
            $allChanged = array_keys((array) $this->new_values);
            $ignoredForDescription = [
                'created_at',
                'updated_at',
                'deleted_at',
                'email_verified_at',
                'last_login',
                'last_login_at',
                'last_password_update',
                'failed_attempts',
                'blocked_until',
                'remember_token',
            ];

            $changed = array_values(array_diff($allChanged, $ignoredForDescription));
            if (empty($changed)) {
                $changed = $allChanged;
            }

            $changedStr = $changed ? implode(', ', $changed) : 'campos';

            if ($this->auditable_type === User::class) {
                $isSelfUpdate = $this->user_id && $this->auditable_id && (int) $this->user_id === (int) $this->auditable_id;

                if ($isSelfUpdate) {
                    return "El usuario actualizó su propio perfil{$labelPart} (cambios: {$changedStr})";
                }

                $actorName = $this->user ? $this->user->name : null;

                if ($actorName) {
                    return "El usuario «{$actorName}» actualizó el perfil de usuario{$labelPart} (cambios: {$changedStr})";
                }

                return "Se actualizó el perfil de usuario{$labelPart} (cambios: {$changedStr})";
            }

            return "Se actualizó {$modelName}{$labelPart} (cambios: {$changedStr})";
        }

        if ($event === 'status_updated') {
            $old = (array) $this->old_values;
            $new = (array) $this->new_values;

            if (array_key_exists('status', $old) && array_key_exists('status', $new)) {
                $normalize = function ($value): bool {
                    if (is_bool($value)) {
                        return $value;
                    }

                    if (is_numeric($value)) {
                        return (bool) $value;
                    }

                    $value = mb_strtolower((string) $value);

                    return in_array($value, ['1', 'true', 'activo', 'active'], true);
                };

                $labelStatus = function (bool $value): string {
                    return $value ? 'Activo' : 'Inactivo';
                };

                $from = $labelStatus($normalize($old['status']));
                $to = $labelStatus($normalize($new['status']));

                if ($from !== $to) {
                    return "Se cambió estado de {$modelName}{$labelPart} de {$from} a {$to}";
                }
            }

            return "Se cambió estado de {$modelName}{$labelPart}";
        }

        if ($event === 'bulk_deleted') {
            return "Se eliminaron múltiples {$modelName}{$labelPart}";
        }

        if ($event === 'pdf_exported') {
            return $this->buildExportDescription($modelName, 'PDF');
        }

        if ($event === 'excel_exported') {
            return $this->buildExportDescription($modelName, 'Excel');
        }

        if ($event === 'csv_exported') {
            return $this->buildExportDescription($modelName, 'CSV');
        }

        if ($event === 'post_approved'){
            return "Se aprobó {$modelName}{$labelPart}";
        }

        if ($event === 'post_rejected'){
            return "Se rechazó {$modelName}{$labelPart}";
        }

        if ($event === 'permissions_updated'){
            return "Se actualizaron permisos del {$modelName}{$labelPart}";
        }

        if ($event === 'profile_updated') {
            $allChanged = array_keys((array) $this->new_values);
            $ignoredForDescription = [
                'created_at',
                'updated_at',
                'deleted_at',
                'email_verified_at',
                'last_login',
                'last_login_at',
                'last_password_update',
                'failed_attempts',
                'blocked_until',
                'remember_token',
            ];

            $changed = array_values(array_diff($allChanged, $ignoredForDescription));
            if (empty($changed)) {
                $changed = $allChanged;
            }

            $changedStr = $changed ? implode(', ', $changed) : 'campos';

            return "El usuario actualizó su perfil{$labelPart} (cambios: {$changedStr})";
        }

        return ucfirst($event) . " de {$modelName}{$labelPart}";
    }

    protected function guessMainLabel(): ?string
    {
        $old = (array) $this->old_values;
        $new = (array) $this->new_values;

        $candidates = ['name', 'nombre', 'title', 'titulo', 'slug'];

        foreach ($candidates as $key) {
            if (array_key_exists($key, $new) && $new[$key] !== null) {
                return (string) $new[$key];
            }

            if (array_key_exists($key, $old) && $old[$key] !== null) {
                return (string) $old[$key];
            }
        }

        // Si no se encuentra en old/new, intentar obtenerlo desde el modelo auditable
        $model = null;

        if ($this->relationLoaded('auditable') && $this->auditable) {
            $model = $this->auditable;
        } elseif ($this->auditable_type && $this->auditable_id) {
            $class = $this->auditable_type;
            if (class_exists($class)) {
                $model = $class::find($this->auditable_id);
            }
        }

        if ($model) {
            foreach ($candidates as $key) {
                if (isset($model->{$key}) && $model->{$key} !== null) {
                    return (string) $model->{$key};
                }
            }
        }

        return null;
    }

    /**
     * Construye una descripción más rica para eventos de exportación
     * (un solo registro, varios seleccionados o todos).
     */
    protected function buildExportDescription(string $modelName, string $format): string
    {
        $values = (array) ($this->new_values ?: $this->old_values ?: []);

        $ids = $values['ids'] ?? null;
        $exportAll = (bool) ($values['export_all'] ?? false);

        // Exportación de todos los registros del módulo
        if ($exportAll) {
            return "Se exportaron todos los registros de {$modelName} a {$format}";
        }

        // Exportación de un único registro por id
        if (is_array($ids) && count($ids) === 1 && $this->auditable_type) {
            $label = null;
            $class = $this->auditable_type;

            if (class_exists($class)) {
                $record = $class::find($ids[0]);
                if ($record) {
                    $candidates = ['name', 'nombre', 'title', 'titulo', 'slug'];
                    foreach ($candidates as $key) {
                        if (isset($record->{$key}) && $record->{$key} !== null) {
                            $label = (string) $record->{$key};
                            break;
                        }
                    }
                }
            }

            $labelPart = $label ? " «{$label}»" : '';

            return "Se exportó {$modelName}{$labelPart} a {$format}";
        }

        // Exportación de registros seleccionados (dos o más)
        if (is_array($ids) && count($ids) >= 2) {
            $count = count($ids);
            return "Se exportaron {$count} registros seleccionados de {$modelName} a {$format}";
        }

        // Caso genérico (por ejemplo, ids null pero sin export_all marcado)
        return "Se exportaron registros de {$modelName} a {$format}";
    }
}
