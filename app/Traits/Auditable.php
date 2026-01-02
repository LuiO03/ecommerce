<?php

namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Arr;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->recordAudit('created');
        });

        static::updated(function ($model) {
            // Solo registrar si realmente hubo cambios
            if (!empty($model->getChanges())) {
                $model->recordAudit('updated');
            }
        });

        static::deleted(function ($model) {
            $model->recordAudit('deleted');
        });
    }

    protected function recordAudit(string $event): void
    {
        try {
            $userId = app()->runningInConsole() ? null : optional(auth()->user())->id;
            $ip = app()->runningInConsole() ? null : optional(request())->ip();
            $userAgent = app()->runningInConsole() ? null : optional(request())->userAgent();

            [$oldValues, $newValues] = $this->resolveAuditValues($event);

            Audit::create([
                'user_id'        => $userId,
                'event'          => $event,
                'auditable_type' => static::class,
                'auditable_id'   => $this->getKey(),
                'old_values'     => $oldValues ?: null,
                'new_values'     => $newValues ?: null,
                'ip_address'     => $ip,
                'user_agent'     => $userAgent,
            ]);
        } catch (\Throwable $e) {
            if (!app()->runningInConsole()) {
                report($e);
            }
        }
    }

    protected function resolveAuditValues(string $event): array
    {
        switch ($event) {
            case 'created':
                return [null, $this->getAttributes()];

            case 'updated':
                $changes = $this->getChanges();
                $old = Arr::only($this->getOriginal(), array_keys($changes));
                return [$old, $changes];

            case 'deleted':
                return [$this->getOriginal(), null];

            default:
                return [null, null];
        }
    }
}
