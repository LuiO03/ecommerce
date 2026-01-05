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
        if (!$this->auditable_type) {
            return 'Sistema';
        }

        return $this->mapAuditableTypeToModuleName($this->auditable_type);
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
                'slug',
            ];

            $changed = array_values(array_diff($allChanged, $ignoredForDescription));
            if (empty($changed)) {
                $changed = $allChanged;
            }

            $changedLabels = $this->mapFieldLabels($changed);
            $changedStr = $changedLabels ? implode(', ', $changedLabels) : 'detalles';

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

        if (in_array($event, [
            'company_general_updated',
            'company_identity_updated',
            'company_contact_updated',
            'company_social_updated',
            'company_legal_updated',
        ], true)) {
            $sections = [
                'company_general_updated'  => 'la información general',
                'company_identity_updated' => 'la identidad visual',
                'company_contact_updated'  => 'los datos de contacto',
                'company_social_updated'   => 'las redes sociales',
                'company_legal_updated'    => 'los aspectos legales',
            ];

            $section = $sections[$event] ?? 'la configuración';

            return $this->buildCompanySettingsDescription($section);
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
                'slug',
            ];

            $changed = array_values(array_diff($allChanged, $ignoredForDescription));
            if (empty($changed)) {
                $changed = $allChanged;
            }

            $changedLabels = $this->mapFieldLabels($changed);
            $changedStr = $changedLabels ? implode(', ', $changedLabels) : 'detalles';

            return "El usuario actualizó su perfil{$labelPart} (cambios: {$changedStr})";
        }

        return ucfirst($event) . " de {$modelName}{$labelPart}";
    }

    protected function buildCompanySettingsDescription(string $section): string
    {
        $old = (array) $this->old_values;
        $new = (array) $this->new_values;

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
            'slug',
        ];

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));

        $changedFields = [];

        foreach ($keys as $field) {
            if (in_array($field, $ignoredForDescription, true)) {
                continue;
            }

            $oldVal = $old[$field] ?? null;
            $newVal = $new[$field] ?? null;

            // Comparar valores; para arrays/objetos usamos JSON para detectar cambios profundos
            if (is_array($oldVal) || is_array($newVal) || is_object($oldVal) || is_object($newVal)) {
                if (json_encode($oldVal) === json_encode($newVal)) {
                    continue;
                }
            } else {
                if ($oldVal === $newVal) {
                    continue;
                }
            }

            $label = $this->mapFieldLabel($field);

            if ($label !== null) {
                $changedFields[] = $label;
            }
        }

        $changedFields = array_values(array_unique($changedFields));
        $changedStr = $changedFields ? implode(', ', $changedFields) : 'detalles';

        return "Se actualizó {$section} de la configuración de la empresa (cambios: {$changedStr})";
    }

    protected function mapFieldLabels(array $fields): array
    {
        $labels = [];

        foreach ($fields as $field) {
            $label = $this->mapFieldLabel($field);

            if ($label !== null) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    protected function mapFieldLabel(string $field): ?string
    {
        $hidden = [
            'id',
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
            'password',
            'created_by',
            'updated_by',
            'deleted_by',
        ];

        if (in_array($field, $hidden, true)) {
            return null;
        }

        $map = [
            // Generales comunes
            'name'              => 'Nombre',
            'nombre'            => 'Nombre',
            'title'             => 'Título',
            'titulo'            => 'Título',
            'slug'              => 'Slug',
            'description'       => 'Descripción',
            'status'            => 'Estado',
            'image'             => 'Imagen',

            // Usuario
            'last_name'         => 'Apellidos',
            'address'           => 'Dirección',
            'dni'               => 'DNI',
            'phone'             => 'Teléfono',
            'image'             => 'Foto de perfil',
            'background_style'  => 'Fondo de perfil',

            // Producto
            'sku'               => 'SKU',
            'price'             => 'Precio',
            'discount'          => 'Descuento',
            'min_stock'         => 'Stock mínimo',
            'category_id'       => 'Categoría',

            // Familia / Categoría
            'family_id'         => 'Familia',
            'parent_id'         => 'Categoría padre',

            // Opciones / Características / Variantes
            'option_id'         => 'Opción',
            'variant_id'        => 'Variante',
            'product_id'        => 'Producto',
            'stock'             => 'Stock',
            'image_path'        => 'Imagen de variante',
            'value'             => 'Valor',

            // Posts / Blog
            'content'           => 'Contenido',
            'views'             => 'Vistas',
            'published_at'      => 'Fecha de publicación',
            'visibility'        => 'Visibilidad',
            'allow_comments'    => 'Permitir comentarios',
            'reviewed_by'       => 'Revisado por',
            'reviewed_at'       => 'Fecha de revisión',

            // Permisos / Seguridad
            'modulo'            => 'Módulo',
            'guard_name'        => 'Guard',

            // Access logs
            'user_id'           => 'Usuario',
            'email'             => 'Correo electrónico',
            'action'            => 'Acción',
            'ip_address'        => 'IP',
            'user_agent'        => 'Agente de usuario',

            // CompanySetting
            'legal_name'        => 'Razón social',
            'ruc'               => 'RUC',
            'slogan'            => 'Eslogan',
            'about'             => 'Acerca de la empresa',
            'primary_color'     => 'Color primario',
            'secondary_color'   => 'Color secundario',
            'logo_path'         => 'Logotipo',
            'support_email'     => 'Correo de soporte',
            'support_phone'     => 'Teléfono de soporte',
            'website'           => 'Sitio web',
            'social_links'      => 'Redes sociales',
            'facebook_enabled'  => 'Facebook habilitado',
            'instagram_enabled' => 'Instagram habilitado',
            'twitter_enabled'   => 'Twitter habilitado',
            'youtube_enabled'   => 'YouTube habilitado',
            'tiktok_enabled'    => 'TikTok habilitado',
            'linkedin_enabled'  => 'LinkedIn habilitado',
            'terms_conditions'  => 'Términos y condiciones',
            'privacy_policy'    => 'Política de privacidad',
            'claims_book_information' => 'Información del libro de reclamaciones',
        ];

        if (isset($map[$field])) {
            return $map[$field];
        }

        // Para otros campos, devolver el nombre tal cual, pero más legible
        return str_replace('_', ' ', $field);
    }

    public function getEventLabelAttribute(): string
    {
        $event = (string) $this->event;

        $map = [
            'created'               => 'Creado',
            'updated'               => 'Actualizado',
            'deleted'               => 'Eliminado',
            'status_updated'        => 'Estado actualizado',
            'bulk_deleted'          => 'Eliminación múltiple',
            'pdf_exported'          => 'PDF exportado',
            'excel_exported'        => 'Excel exportado',
            'csv_exported'          => 'CSV exportado',
            'post_approved'         => 'Publicación aprobada',
            'post_rejected'         => 'Publicación rechazada',
            'permissions_updated'   => 'Permisos actualizados',
            'profile_updated'       => 'Perfil actualizado',
            'company_general_updated'  => 'Empresa · Información general',
            'company_identity_updated' => 'Empresa · Identidad visual',
            'company_contact_updated'  => 'Empresa · Datos de contacto',
            'company_social_updated'   => 'Empresa · Redes sociales',
            'company_legal_updated'    => 'Empresa · Aspectos legales',
        ];

        return $map[$event] ?? ucfirst($event);
    }

    protected function mapAuditableTypeToModuleName(string $auditableType): string
    {
        $shortName = class_basename($auditableType);

        $map = [
            'User'            => 'Usuario',
            'Role'            => 'Rol',
            'Family'          => 'Familia',
            'Category'        => 'Categoría',
            'Product'         => 'Producto',
            'Post'            => 'Publicación',
            'CompanySetting'  => 'Configuración de la empresa',
            'Audit'           => 'Auditoría',
            'Option'          => 'Opción',
        ];

        return $map[$shortName] ?? $shortName;
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
