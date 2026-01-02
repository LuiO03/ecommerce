<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
            return "Creación de {$modelName}{$labelPart}";
        }

        if ($event === 'deleted') {
            return "Eliminación de {$modelName}{$labelPart}";
        }

        if ($event === 'updated') {
            $changed = array_keys((array) $this->new_values);
            $changedStr = $changed ? implode(', ', $changed) : 'campos';

            return "Actualización de {$modelName}{$labelPart} (cambios: {$changedStr})";
        }

        if ($event === 'status_updated') {
            return "Cambio de estado de {$modelName}{$labelPart}";
        }

        if ($event === 'bulk_deleted') {
            return "Eliminación múltiple de {$modelName}{$labelPart}";
        }

        if ($event === 'pdf_exported') {
            return "Exportación de {$modelName}{$labelPart} a PDF";
        }

        if ($event === 'excel_exported') {
            return "Exportación de {$modelName}{$labelPart} a Excel";
        }

        if ($event === 'csv_exported') {
            return "Exportación de {$modelName}{$labelPart} a CSV";
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

        return null;
    }
}
